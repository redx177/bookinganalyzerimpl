<?php

class KPrototypeAlgorithm
{
    private $bookingsCount;
    private $bookingsCountCap;
    private $maxIterations;
    private $k;

    /**
     * @var DistanceMeasurement
     */
    private $distance;
    /**
     * @var Random
     */
    private $random;
    /**
     * @var Redis
     */
    private $redis;
    /**
     * @var BookingDataIterator
     */
    private $bookingDataIterator;
    /**
     * @var BookingDataIterator
     */
    private $bookingDataIterator2;
    /**
     * @var ClusteringProgress
     */
    private $progress;
    /**
     * @var BookingBuilder
     */
    private $bookingBuilder;

    public function __construct(
        ConfigProvider $config,
        DistanceMeasurement $distance,
        Random $random,
        Redis $redis,
        BookingDataIterator $bookingDataIterator,
        BookingDataIterator $bookingDataIterator2,
        ClusteringProgress $progress,
        BookingBuilder $bookingBuilder)
    {
        $this->distance = $distance;
        $this->bookingsCountCap = $config->get('bookingsCountCap');
        $this->redis = $redis;
        $this->bookingDataIterator = $bookingDataIterator;
        $this->bookingDataIterator2 = $bookingDataIterator2;
        $this->progress = $progress;
        $this->random = $random;

        $kprototypeConfig = $config->get('kprototype');
        $this->stopFile = $kprototypeConfig['serviceStopFile'];
        $this->maxIterations = $kprototypeConfig['maxIterations'];
        $this->k = $kprototypeConfig['k'];

        $this->bookingsCount = $this->bookingDataIterator->count();
        if ($this->bookingsCountCap) {
            $this->bookingsCount = $this->bookingsCountCap;
        }
        $this->bookingBuilder = $bookingBuilder;
    }

    /**
     * Runs the kprototype clustering algorithm.
     */
    public function run(): ClusteringResult
    {
        $iteration = 1;

        // Initialization
        $clusters = $this->getInitialEmptyClusters($this->k);
        $this->storeState($clusters, 0);

        $this->assignBookingsToClusters($clusters);

        $currentTotalCosts = $clusters->getTotalCosts();
        $bestTotalCosts = $currentTotalCosts;
        $bestClusters = $clusters;
        $delta = 1;
        while ($delta > 0 && $iteration <= $this->maxIterations) {
            $this->storeState($clusters, 0);

            foreach ($clusters->getClusters() as $clusterToReplace) {

                $offset = 0;
                foreach ($this->bookingDataIterator as $newClusterCenter) {
                    if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                        break;
                    }
                    $offset++;

                    // If $booking is already a center, skip it.
                    if (in_array($newClusterCenter->getId(), $clusters->getClusterCenterIds())) {
                        continue;
                    }

                    $newClusters = $this->generateNewEmptyClustersWithSwapedCenter($clusters, $clusterToReplace, $newClusterCenter, $iteration);
                    $this->assignBookingsToClusters($newClusters);
                    $newTotalCosts = $newClusters->getTotalCosts();
                    if ($newTotalCosts < $bestTotalCosts) {
                        $bestTotalCosts = $newTotalCosts;
                        $bestClusters = $newClusters;
                    }
                    if ($offset % 100 == 0) {
                        $this->storeState($bestClusters, 0);
                    }
                }

                $this->bookingDataIterator->rewind();
            }

            $delta = $currentTotalCosts - $bestTotalCosts;
            $clusters = $bestClusters;
            $currentTotalCosts = $bestTotalCosts;
            $iteration++;
        }
        $this->storeState($clusters, 1);
        return $clusters;
    }

    /**
     * Gets a Clusters object with $k clusters inside with no Associates added yet.
     * @param int $k Number of clusters.
     * @return KPrototypeResult Clusters object with $k clusters inside.
     */
    private function getInitialEmptyClusters(int $k): KPrototypeResult
    {
        if ($k > $this->bookingsCount) {
            $k = $this->bookingsCount;
        }

        $clusters = new KPrototypeResult(1);
        $clusterCenterIndices = [];
        for ($i = 0; $i < $k; $i++) {
            $clusterCenterIndex = $this->random->generate($this->bookingsCount);

            // If index is already set, rerun generation.
            if (in_array($clusterCenterIndex, $clusterCenterIndices)) {
                $i--;
            } else {
                $clusterCenterIndices[] = $clusterCenterIndex;
                $rawBooking = $this->redis->hGetAll($clusterCenterIndex);
                $booking = $this->bookingBuilder->fromRawData($rawBooking);
                $clusters->addCluster(new KPrototypeCluster($booking));
            }
        }
        return $clusters;
    }

    /**
     * Assignes all bookings from the bookingsProvider to the $clusters.
     * @param KPrototypeResult $clusters Clusters to add the bookings to.
     */
    private function assignBookingsToClusters(KPrototypeResult $clusters)
    {
        $clusterCenterIds = $clusters->getClusterCenterIds();

        $offset = 0;
        foreach ($this->bookingDataIterator2 as $booking) {
            if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                break;
            }
            if (in_array($booking->getId(), $clusterCenterIds)) {
                continue;
            }
            $this->assignBookingToCluster($clusters, $booking);
            $offset++;
        }
        $this->bookingDataIterator2->rewind();
    }

    /**
     * Adds the given $booking to the closest cluster in $clusters.
     * @param KPrototypeResult $clusters Clusters to add the booking to.
     * @param Booking $booking Booking to add to a cluster.
     */
    private function assignBookingToCluster(KPrototypeResult $clusters, Booking $booking)
    {
        $closestCluster = null;
        $closestDistance = null;

        foreach ($clusters->getClusters() as $cluster) {
            $distance = $this->distance->measure($cluster->getCenter(), $booking);
            if ($closestDistance === null || $closestDistance > $distance) {
                $closestDistance = $distance;
                $closestCluster = $cluster;
            }
        }
        $closestCluster->addClusterPoint(new DistanceClusterPoint($booking, $closestDistance));
    }

    /**
     * Creates a new Clusters object with one Cluster replaced.
     * @param KPrototypeResult $oldClusters Old Clusters.
     * @param KPrototypeCluster $clusterToReplace Cluster in $oldClusters which should not be placed into the new Clusters object..
     * @param Booking $newCenter New Center to add to the new Clusters object and should replace the $clusterToReplace.
     * @param int $iteration Current iteration number.
     * @return KPrototypeResult New Clusters object with one Cluster replaced.
     */
    private function generateNewEmptyClustersWithSwapedCenter(KPrototypeResult $oldClusters, KPrototypeCluster $clusterToReplace, Booking $newCenter, int $iteration)
    {
        $newClusters = new KPrototypeResult($iteration);
        foreach ($oldClusters->getClusters() as $oldCluster) {
            if ($oldCluster->getCenter()->getId() != $clusterToReplace->getCenter()->getId()) {
                $newClusters->addCluster(new KPrototypeCluster($oldCluster->getCenter()));
            }
        }
        $newClusters->addCluster(new KPrototypeCluster($newCenter));
        return $newClusters;
    }

    /**
     * @param int $status 0 = Data caching done. 1 = Clustering done. 2 = Apriori done.
     */
    private function storeState(KPrototypeResult $clusters, int $status)
    {
        $this->progress->storeState($this->bookingsCount, $clusters, $status);
    }
}