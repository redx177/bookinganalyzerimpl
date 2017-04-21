<?php
use \DI\FactoryInterface;

class AprioriAlgorithm
{
    private $bookingsProvider;
    private $bookingsCount = 0;
    private $bookingsCountCap;
    private $lastOutput;
    private $outputFile;
    private $outputInterval;
    private $startTime;
    private $fieldNameMapping;
    private $rootDir;
    /**
     * @var AprioriProgress
     */
    private $progress;
    /**
     * @var BookingDataIterator
     */
    private $bookingDataIterator;
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * AprioriAlgorithm constructor.
     * @param BookingsProvider $bookingsProvider Provider for the data to analyze.
     * @param BookingDataIterator $bookingDataIterator Booking data iterator.
     * @param ConfigProvider $config Configuration provider.
     * @param AprioriProgress $progress Processes the progress of the apriori algorithm.
     * @param FactoryInterface $factory Dependency injection factory to make objects.
     */
    public function __construct(BookingsProvider $bookingsProvider,
                                BookingDataIterator $bookingDataIterator,
                                ConfigProvider $config,
                                AprioriProgress $progress,
                                FactoryInterface $factory)
    {
        $this->bookingsProvider = $bookingsProvider;
        $this->progress = $progress;
        $this->bookingDataIterator = $bookingDataIterator;
        $this->factory = $factory;

        $this->lastOutput = microtime(TRUE);
        $this->startTime = microtime(TRUE);

        $this->bookingsCountCap = $config->get('bookingsCountCap');
        $this->fieldNameMapping = $config->get('fieldNameMapping');
        $this->rootDir = $config->get('rootDir');

        $aprioriConfig = $config->get('apriori');
        $this->minSup = $aprioriConfig['minSup'];
        $this->stopFile = $aprioriConfig['serviceStopFile'];
        $this->outputInterval = $aprioriConfig['outputInterval'];
        $this->outputFile = $aprioriConfig['serviceOutput'];
    }

    /**
     * Analyzes the bookings with the apriori algorithm and a file as datasource.
     * @return Histograms Histograms representing the results.
     */
    public function run() : Histograms
    {
        $frequentSets = [];
        $frequentSets[0] = $this->getInitialFrequentSets();
        for ($i = 1; true && !$this->isStopScriptSet(); $i++) {
            $candidates = $this->aprioriGen($frequentSets[$i-1]);
            if (!$candidates) {
                break;
            }
            $countedCandidates = $this->countCandidates($candidates, $frequentSets);
            $frequentSet = $this->filterByMinSup($countedCandidates, $this->bookingsCount);
            usort($frequentSet, array('AprioriAlgorithm', 'frequentSetSort'));
            $frequentSets[$i] = $frequentSet;
        }
        $this->storeState(null, $frequentSets);
        return $this->generateHistograms($frequentSets, $this->bookingsCount);
    }


    /**
     * Analyzes the bookings with the apriori algorithm and a provided Clusters as datasource.
     * @param Clusters $clusters Clusters to analyze. Each Cluster will be anaylzed with the apriori algorithm.
     * @return Clusters with attached histograms.
     */
    public function runOnClusters(Clusters $clusters): Clusters {
        foreach ($clusters->getClusters() as $cluster) {
            $this->bookingDataIterator = $this->factory->make(LoadClusterDataIterator::class, ['cluster' => $cluster]);
            $cluster->setHistograms($this->run());
        }
        return $clusters;
    }

    private function getInitialFrequentSets(): array
    {
        /** @var Field[] $fields */
        $candidates = [];
        $offset = 0;
        foreach ($this->bookingDataIterator as $rawBooking) {
            if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                break;
            }
            $offset++;

            $booking = $this->bookingsProvider->getBooking($rawBooking);
            $fields = array_merge(
                $booking->getFieldsByType(BooleanField::class),
                $booking->getFieldsByType(IntegerField::class),
                $booking->getFieldsByType(DistanceField::class),
                $booking->getFieldsByType(PriceField::class));

            foreach ($fields as $field) {
                if ($field->hasValue()) {
                    $name = $field->getName();
                    $value = $field->getValue();
                    if (!array_key_exists($name . $value, $candidates)) {
                        $candidates[$name . $value] = [[$name=>$value], 0];
                    }
                    $candidates[$name . $value] = [[$name=>$value], $candidates[$name . $value][1]+1];
                }
            }

            if ($offset % 1000 == 0) {
                $this->storeState($candidates);
            }

        }

        $frequentSet = $this->filterByMinSup($candidates, $this->bookingsCount);
        usort($frequentSet, array('AprioriAlgorithm', 'frequentSetSort'));
        return $frequentSet;
    }

    private function countCandidates($candidates, $frequentSets): array
    {
        $countedCandidates = [];
        $offset = 0;
        foreach ($this->bookingDataIterator as $rawBooking) {
            if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                break;
            }
            $offset++;

            $booking = $this->bookingsProvider->getBooking($rawBooking);
            foreach ($candidates as $candidate) {
                $id = [];
                $c = [];
                foreach ($candidate as $key => $value) {
                    $field = $booking->getFieldByName($key);
                    if (!$field->hasValue() || $field->getValue() != $value) {
                        // This candidate does not match this booking, so go to next candidate.
                        continue 2;
                    }
                    $id[$key] = $value;
                    $c[$key] = $value;
                }

                $k = $this->getUniqueKey($id);

                if (!array_key_exists($k, $countedCandidates)) {
                    $countedCandidates[$k] = [$c, 0];
                }
                $countedCandidates[$k] = [$c, $countedCandidates[$k][1]+1];
            }
            if ($offset % 1000 == 0) {
                $this->storeState($countedCandidates, $frequentSets);
            }
        }
        $this->bookingDataIterator->rewind();
        return $countedCandidates;
    }

    private function filterByMinSup(array $candidates, $bookingsCount)
    {
        $frequentSet = [];
        foreach ($candidates as $key => $value) {
            if ($value[1] >= $bookingsCount * $this->minSup) {
                $frequentSet[$key] = $value;
            }
        }
        return $frequentSet;
    }

    private function generateHistograms(array $frequentSets, int $bookingsCount)
    {
        $setSize = 1;
        $histograms = new Histograms();
        foreach ($frequentSets as $frequentSet) {
            if (count($frequentSet) === 0) {
                break;
            }

            $histogram = new Histogram($setSize);
            foreach ($frequentSet as $key => $value) {
                $histogram->addHistogramBin(new HistogramBin($value[0], $value[1], $bookingsCount));
            }
            $histograms->addHistogram($histogram);
            $setSize++;
        }

        return $histograms;
    }

    private function aprioriGen(array $oldFrequentSet)
    {
        $candidates = [];
        foreach ($oldFrequentSet as $frequentItem1) {
            $base = $frequentItem1[0];
            foreach ($oldFrequentSet as $frequentItem2) {
                $additionalItems = $frequentItem2[0];
                foreach ($additionalItems as $key => $additionalItem) {
                    $candidate = $base;
                    $candidate[$key] = $additionalItem;
                    if (count($candidate) == count($base)+1) {
                        // Generate unique key for the fields to prevent duplicates.
                        $k = $this->getUniqueKey($candidate);
                        $candidates[$k] = $candidate;
                    }
                }
            }
        }
        return $candidates;
    }

    private function storeState($candidates = null, $frequentSets = null)
    {
        $this->progress->storeState($this->startTime, $this->bookingsCount, $candidates, $frequentSets);
    }

    static function frequentSetSort($a, $b) {
        if ($a[1] == $b[1]) {
            return 0;
        }
        return $a[1] > $b[1] ? -1 : 1;
    }

    private function isStopScriptSet()
    {
        return file_exists($this->rootDir . $this->stopFile);
    }

    /**
     * Generates an unique key for the array.
     * Order of the elements does not matter.
     * Sorts the array by key first so [k1=>v1,k2=>v2] and [k2=>v2,k1=>v1] generate the same key.
     * @param array $array Array to create the unique key for.
     * @return string Unique key.
     */
    private function getUniqueKey($array): string
    {
        ksort($array);
        return implode(array_keys($array)) . implode(array_values($array));
    }

    /**
     * Gets the amount of bookings.
     * @return int Bookings count.
     */
    public function getBookingsCount()
    {
        return $this->bookingsCount;
    }
}