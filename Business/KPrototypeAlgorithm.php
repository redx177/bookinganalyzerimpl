<?php

class KPrototypeAlgorithm
{
    private $bookingsCount;
    private $bookingsCountCap;
    private $outputFile;
    private $outputInterval;
    private $fieldNameMapping;
    private $rootDir;
    private $maxIterations;

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
        $this->fieldNameMapping = $config->get('fieldNameMapping');
        $this->rootDir = $config->get('rootDir');
        $this->redis = $redis;
        $this->bookingDataIterator = $bookingDataIterator;
        $this->bookingDataIterator2 = $bookingDataIterator2;
        $this->progress = $progress;
        $this->random = $random;

        $kprototypeConfig = $config->get('kprototype');
        $this->stopFile = $kprototypeConfig['serviceStopFile'];
        $this->outputInterval = $kprototypeConfig['outputInterval'];
        $this->outputFile = $kprototypeConfig['serviceOutput'];
        $this->maxIterations = $kprototypeConfig['maxIterations'];

        $this->bookingsCount = $this->bookingDataIterator->count();
        if ($this->bookingsCountCap) {
            $this->bookingsCount = $this->bookingsCountCap;
        }
        $this->bookingBuilder = $bookingBuilder;
    }

    /**
     * Analyzes the bookings with the apriori algorithm.
     * @return Clusters
     */
    public function run(): Clusters
    {
        $k = 2;
        $iteration = 1;

        // Initialization
        $clusters = $this->getInitialEmptyClusters($k);
        $this->storeState($clusters, 0);

//        echo "init\n";
//        $this->debug($clusters);
//        $start = microtime(true);

        $this->assignBookingsToClusters($clusters);

//        echo "\nRuntime: " . (microtime(true) - $start);

//        echo "first count\n";
//        $this->debug($clusters);

        $currentTotalCosts = $clusters->getTotalCosts();
        $bestTotalCosts = $currentTotalCosts;
        $bestClusters = $clusters;
        $delta = 1;
        while ($delta > 0 && $iteration <= $this->maxIterations) {
            $this->storeState($clusters, 0);

//            echo "main loop\n";
//            echo "Iteration: {$iteration}\n";
//            $this->debug($clusters);

            foreach ($clusters->getClusters() as $clusterToReplace) {

//                echo "\nCluster center id: {$cluster->getCenter()->getId()}\n";

                $offset = 0;
                foreach ($this->bookingDataIterator as $newClusterCenter) {
//                    echo 1;
                    if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
//                        echo "\nbookingsCountCap reached. Break!\n";
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

//            echo "\nRuntime: " . (microtime(true) - $start);
//            echo "\nCurrentTotalCosts: {$currentTotalCosts}";
//            echo "\nBestTotalCosts: {$bestTotalCosts}";
//            echo "\nDelta: {$delta}\n-------------------------------------\n";
        }
//        echo "done!\n";
        $this->storeState($clusters, 1);
//        echo "\nRuntime: " . (microtime(true) - $start);
        return $clusters;
    }

    /**
     * Gets a Clusters object with $k clusters inside with no Associates added yet.
     * @param int $k Number of clusters.
     * @return Clusters Clusters object with $k clusters inside.
     */
    private function getInitialEmptyClusters(int $k): Clusters
    {
        if ($k > $this->bookingsCount) {
            $k = $this->bookingsCount;
        }

        $clusters = new Clusters(1);
        $clusterCenterIndices = [];
        for ($i = 0; $i < $k; $i++) {
            $clusterCenterIndex = $this->random->generate(100);
//            echo "Center index: {$clusterCenterIndex}\n";

            // If index is already set, rerun generation.
            if (in_array($clusterCenterIndex, $clusterCenterIndices)) {
                $i--;
            } else {
                $clusterCenterIndices[] = $clusterCenterIndex;
                $rawBooking = $this->redis->hGetAll($clusterCenterIndex);
                $booking = $this->bookingBuilder->fromRawData($rawBooking);
                $clusters->addCluster(new Cluster($booking));
            }
        }
        return $clusters;
    }

    /**
     * Assignes all bookings from the bookingsProvider to the $clusters.
     * @param Clusters $clusters Clusters to add the bookings to.
     */
    private function assignBookingsToClusters(Clusters $clusters)
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
     * @param Clusters $clusters Clusters to add the booking to.
     * @param Booking $booking Booking to add to a cluster.
     */
    private function assignBookingToCluster(Clusters $clusters, Booking $booking)
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
        $closestCluster->addAssociate(new Associate($booking, $closestDistance));
    }

    /**
     * Creates a new Clusters object with one Cluster replaced.
     * @param Clusters $oldClusters Old Clusters.
     * @param Cluster $clusterToReplace Cluster in $oldClusters which should not be placed into the new Clusters object..
     * @param Booking $newCenter New Center to add to the new Clusters object and should replace the $clusterToReplace.
     * @param int $iteration Current iteration number.
     * @return Clusters New Clusters object with one Cluster replaced.
     */
    private function generateNewEmptyClustersWithSwapedCenter(Clusters $oldClusters, Cluster $clusterToReplace, Booking $newCenter, int $iteration)
    {
        $newClusters = new Clusters($iteration);
        foreach ($oldClusters->getClusters() as $oldCluster) {
            if ($oldCluster->getCenter()->getId() != $clusterToReplace->getCenter()->getId()) {
                $newClusters->addCluster(new Cluster($oldCluster->getCenter()));
            }
        }
        $newClusters->addCluster(new Cluster($newCenter));
        return $newClusters;
    }

    /**
     * @param int $status 0 = Data caching done. 1 = Clustering done. 2 = Apriori done.
     */
    private function storeState(Clusters $clusters, int $status)
    {
        $this->progress->storeState($this->bookingsCount, $clusters, $status);
    }

    /**
     * @param Clusters $clusters
     * @return array
     */
    protected function debug($clusters): array
    {
        $count0 = count($clusters->getClusters()[0]->getAssociates());
        $count1 = count($clusters->getClusters()[1]->getAssociates());
        echo "ID1: {$clusters->getClusters()[0]->getCenter()->getId()}\n";
        echo "ID2: {$clusters->getClusters()[1]->getCenter()->getId()}\n";
        echo "Associates1: {$count0}\n";
        echo "Associates2: {$count1}\n";
        echo "TotalCosts: {$clusters->getTotalCosts()}\n----------------------------------\n";
        return array($count0, $count1);
    }
}