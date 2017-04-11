<?php

class KPrototypeAlgorithm
{
    private $bookingsProvider;
    private $bookingsCount;
    private $bookingsCountCap;
    private $lastOutput;
    private $outputFile;
    private $outputInterval;
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
     * @var Random
     */
    private $random;

    /**
     * AprioriAlgorithm constructor.
     * @param BookingsProvider $bookingsProvider Provider for the data to analyze.
     * @param ConfigProvider $config Configuration provider.
     * @param DistanceMeasurement $distance Measures distance between two bookings.
     * @param Random $random Generate random numbers.
     * @param Twig_TemplateWrapper|null $template Template to render the wip to.
     */
    public function __construct(
        BookingsProvider $bookingsProvider,
        ConfigProvider $config,
        DistanceMeasurement $distance,
        Random $random,
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
        $this->random = $random;
    }

    /**
     * Analyzes the bookings with the apriori algorithm.
     * @param Filters|null $filters Filter set for the bookings.
     * @return Cluster[]
     */
    public function run(Filters $filters = null): array
    {
        $this->bookingsCount = $this->getBookingsCount($filters);
        $this->bookingsProvider->rewind();

        $clusters = $this->getInitialEmptyClusters(2, $filters);
        $clusterCenterIds = $this->getClusterCenterIds($clusters);
        $this->bookingsProvider->rewind();

        $this->assignBookingsToClusters($clusters, $clusterCenterIds, $filters);
        return $clusters;
    }

    private function getInitialEmptyClusters(int $k, Filters $filters = null): array
    {
        if ($k > $this->bookingsCount) {
            $k = $this->bookingsCount;
        }

        $clusterCenterIndices = [];
        for ($i = 0; $i < $k; $i++) {
            $max = $this->bookingsCountCap && $this->bookingsCountCap < $this->bookingsCount
                ? $this->bookingsCountCap - 1
                : $this->bookingsCount - 1;
            $clusterCenterIndex = $this->random->generate($max);

            // If index is already set, rerun generation.
            if (in_array($clusterCenterIndex, $clusterCenterIndices)) {
                $i--;
            } else {
                $clusterCenterIndices[] = $clusterCenterIndex;
            }
        }
        sort($clusterCenterIndices);

        $offset = 0;
        $batchSize = 1000;
        $clusters = [];
        while (!$this->bookingsProvider->hasEndBeenReached()) {
            if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                break;
            }
            $bookings = $this->bookingsProvider->getSubset($batchSize, $filters);
            foreach ($bookings as $booking) {
                if (in_array($offset, $clusterCenterIndices)) {
                    $clusters[] = new Cluster($booking);
                }
                $offset++;
            }
        }
        return $clusters;
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

    /**
     * @param Cluster[] $clusters
     * @param int[] $clusterCenterIds
     * @param Filters|null $filters
     */
    private function assignBookingsToClusters(array $clusters, array $clusterCenterIds, Filters $filters = null)
    {
        $offset = 0;
        $batchSize = 1000;
        while (!$this->bookingsProvider->hasEndBeenReached()) {
            if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                break;
            }
            $bookings = $this->bookingsProvider->getSubset($batchSize, $filters);
            $this->bookingsCount += count($bookings);
            foreach ($bookings as $booking) {
                if (in_array($booking->getId(), $clusterCenterIds)) {
                    continue;
                }
                $this->assignBookingToCluster($clusters, $booking);
            }
        }
    }

    /**
     * @param Cluster[] $clusters
     * @param Booking $booking
     */
    private function assignBookingToCluster(array $clusters, Booking $booking)
    {
        $closestCluster = null;
        $closestDistance = null;

        foreach ($clusters as $cluster) {
            $distance = $this->distance->measure($cluster->getCenter(), $booking);
            if ($closestDistance === null || $closestDistance > $distance) {
                $closestDistance = $distance;
                $closestCluster = $cluster;
            }
        }
        $closestCluster->addAssociate(new Associate($booking, $closestDistance));
    }

    /**
     * @param Cluster[] $clusters
     * @return int[]
     */
    private function getClusterCenterIds(array $clusters)
    {
        $ids = [];
        foreach ($clusters as $cluster) {
            $ids[] = $cluster->getCenter()->getId();
        }
        return $ids;
    }
}