<?php

class KPrototypeAlgorithm
{
    private $bookingsProvider;
    private $bookingsCount;
    private $bookingsCountCap;
    private $lastOutput;
    private $outputFile;
    private $outputInterval;
    private $fileWriteCount = 0;
    private $startTime;
    private $fieldNameMapping;
    private $rootDir;

    /**
     * @var Twig_TemplateWrapper
     */
    private $template;
    /**
     * @var DistanceMeasurement
     */
    private $distance;

    /**
     * AprioriAlgorithm constructor.
     * @param BookingsProvider $bookingsProvider Provider for the data to analyze.
     * @param ConfigProvider $config Configuration provider.
     * @param DistanceMeasurement $distance Measures distance between two bookings.
     * @param Twig_TemplateWrapper|null $template Template to render the wip to.
     */
    public function __construct(
        BookingsProvider $bookingsProvider,
        ConfigProvider $config,
        DistanceMeasurement $distance,
        Twig_TemplateWrapper $template = null)
    {
        $this->bookingsProvider = $bookingsProvider;
        $this->distance = $distance;
        $this->template = $template;
        $this->bookingsCountCap = $config->get('bookingsCountCap');
        $this->fieldNameMapping = $config->get('fieldNameMapping');
        $this->rootDir = $config->get('rootDir');
        $this->lastOutput = microtime(TRUE);
        $this->startTime = microtime(TRUE);

        $kprototypeConfig = $config->get('kprototype');
        $this->stopFile = $kprototypeConfig['serviceStopFile'];
        $this->outputInterval = $kprototypeConfig['outputInterval'];
        $this->outputFile = $kprototypeConfig['serviceOutput'];
    }

    /**
     * Analyzes the bookings with the apriori algorithm.
     * @param Filters|null $filters Filter set for the bookings.
     * @return Histograms Histograms representing the results.
     */
    public function run(Filters $filters = null) : Histograms
    {
        $this->bookingsCount = $this->getBookingsCount($filters);
        $this->bookingsProvider->rewind();

        $prototypes = $this->getPrototypes(2, $filters);
        $this->bookingsProvider->rewind();

        $this->assignBookingsToPrototype($prototypes, $filters);
    }

    private function getPrototypes(int $k, Filters $filters = null): array
    {
        if ($k > $this->bookingsCount) {
            $k = $this->bookingsCount;
        }

        $prototypeIndicies = [];
        for ($i = 0; $i < $k; $i++) {
            $max = $this->bookingsCountCap && $this->bookingsCountCap < $this->bookingsCount
                ? $this->bookingsCountCap - 1
                : $this->bookingsCount - 1;
            $prototypeIndex = mt_rand(0, $max);

            // If index is already set, rerun generation.
            if (in_array($prototypeIndex, $prototypeIndicies)) {
                $i--;
            } else {
                $prototypeIndicies[] = $prototypeIndex;
            }
        }
        sort($prototypeIndicies);

        $offset = 0;
        $batchSize = 1000;
        $prototypes = [];
        while (!$this->bookingsProvider->hasEndBeenReached()) {
            if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                break;
            }
            $bookings = $this->bookingsProvider->getSubset($batchSize, $filters);
            foreach ($bookings as $booking) {
                if (in_array($offset, $prototypeIndicies)) {
                    $prototypes[] = new Prototype($booking);
                }
                $offset++;
            }
        }
        return $prototypes;
    }

    private function getBookingsCount($filters): int
    {
        $offset = 0;
        $batchSize = 1000;
        $bookingsCount = 0;
        while (!$this->bookingsProvider->hasEndBeenReached()) {
            if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                break;
            }
            $bookings = $this->bookingsProvider->getSubset($batchSize, $filters);
            $bookingsCount += count($bookings);
        }
        return $bookingsCount;
    }

    private function assignBookingsToPrototype(array $prototypes, Filters $filters = null)
    {
        /* var $protoypes Prototype[] */
        $offset = 0;
        $batchSize = 1000;
        while (!$this->bookingsProvider->hasEndBeenReached()) {
            if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                break;
            }
            $bookings = $this->bookingsProvider->getSubset($batchSize, $filters);
            $this->bookingsCount += count($bookings);
            foreach ($bookings as $booking) {
                $this->assignBookingToPrototype($prototypes, $booking);
            }
        }
    }

    /**
     * @param Prototype[] $prototypes
     * @param Booking $booking
     */
    private function assignBookingToPrototype(array $prototypes, Booking $booking)
    {
        // [Prototype, Distance]
        $closestPrototype = [null, 0];

        foreach ($prototypes as $prototype) {
            $distance = $this->distance->measure($prototype->getPrototypeBooking(), $booking);
            if ($closestPrototype[1] > $distance) {
                $closestPrototype = [$prototype, $distance];
            }
        }
        $closestPrototype[0]->addBooking($booking);
    }
}