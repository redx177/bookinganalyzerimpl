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
    private $maxIterations;

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
     * @var Redis
     */
    private $redis;
    /**
     * @var LoadRedisDataIterator
     */
    private $redisIterator;
    /**
     * @var ClusteringProgress
     */
    private $progress;

    /**
     * AprioriAlgorithm constructor.
     * @param BookingsProvider $bookingsProvider Provider for the data to analyze.
     * @param ConfigProvider $config Configuration provider.
     * @param DistanceMeasurement $distance Measures distance between two bookings.
     * @param Random $random Generate random numbers.
     * @param Redis $redis Redis store.
     * @param LoadRedisDataIterator $redisIterator Redis iterator.
     * @param ClusteringProgress $progress
     * @internal param Redis $redis Redis datastore.
     */
    public function __construct(
        BookingsProvider $bookingsProvider,
        ConfigProvider $config,
        DistanceMeasurement $distance,
        Random $random,
        Redis $redis,
        LoadRedisDataIterator $redisIterator,
        ClusteringProgress $progress)
    {
        $this->bookingsProvider = $bookingsProvider;
        $this->distance = $distance;
        $this->bookingsCountCap = $config->get('bookingsCountCap');
        $this->fieldNameMapping = $config->get('fieldNameMapping');
        $this->rootDir = $config->get('rootDir');
        $this->lastOutput = microtime(TRUE);
        $this->startTime = microtime(TRUE);
        $this->redis = $redis;
        $this->redisIterator = $redisIterator;
        $this->progress = $progress;
        $this->random = $random;

        $kprototypeConfig = $config->get('kprototype');
        $this->stopFile = $kprototypeConfig['serviceStopFile'];
        $this->outputInterval = $kprototypeConfig['outputInterval'];
        $this->outputFile = $kprototypeConfig['serviceOutput'];
        $this->maxIterations = $kprototypeConfig['maxIterations'];
    }

    /**
     * Analyzes the bookings with the apriori algorithm.
     * @param Filters|null $filters Filter set for the bookings.
     * @return Clusters
     */
    public function run(Filters $filters = null): Clusters
    {
        $this->dataProvider =
        $k = 2;
        $this->bookingsCount = $this->redisIterator->count();

        // Initialization
        $clusters = $this->getInitialEmptyClusters($k, $filters);
        $this->storeState($clusters, 0);

        $count0 = count($clusters->getClusters()[0]->getAssociates());
        $count1 = count($clusters->getClusters()[1]->getAssociates());
        echo "init\n";
        echo "ID1: {$clusters->getClusters()[0]->getCenter()->getId()}\n";
        echo "ID2: {$clusters->getClusters()[1]->getCenter()->getId()}\n";
        echo "Associates1: {$count0}\n";
        echo "Associates2: {$count1}\n";
        echo "TotalCosts: {$clusters->getTotalCosts()}\n----------------------------------\n";

        $this->assignBookingsToClusters($clusters, $filters);

        $count0 = count($clusters->getClusters()[0]->getAssociates());
        $count1 = count($clusters->getClusters()[1]->getAssociates());
        echo "first count\n";
        echo "ID1: {$clusters->getClusters()[0]->getCenter()->getId()}\n";
        echo "ID2: {$clusters->getClusters()[1]->getCenter()->getId()}\n";
        echo "Associates1: {$count0}\n";
        echo "Associates2: {$count1}\n";
        echo "TotalCosts: {$clusters->getTotalCosts()}\n----------------------------------\n";

        $currentTotalCosts = $clusters->getTotalCosts();
        $bestTotalCosts = $currentTotalCosts;
        $bestClusters = $clusters;
        $delta = 1;
        $iteration = 1;
        while ($delta > 0 && $iteration <= $this->maxIterations) {
            $iteration++;
            $this->storeState($clusters, $iteration);

            $count0 = count($clusters->getClusters()[0]->getAssociates());
            $count1 = count($clusters->getClusters()[1]->getAssociates());
            echo "main loop\n";
            echo "Iteration: {$iteration}\n";
            echo "ID1: {$clusters->getClusters()[0]->getCenter()->getId()}\n";
            echo "ID2: {$clusters->getClusters()[1]->getCenter()->getId()}\n";
            echo "Associates1: {$count0}\n";
            echo "Associates2: {$count1}\n";
            echo "TotalCosts: {$clusters->getTotalCosts()}\n----------------------------------\n";
            foreach ($clusters->getClusters() as $cluster) {

                echo "\nCluster center id: {$cluster->getCenter()->getId()}\n";

                $offset = 0;
                foreach ($this->redisIterator as $rawBooking) {
                    echo 1;
                    if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                        echo "\nbookingsCountCap reached. Break!\n";
                        break;
                    }
                    $offset++;

                    $booking = $this->bookingsProvider->getBooking($rawBooking);

                    // If $booking is already a center, skip it.
                    if (in_array($booking->getId(), $clusters->getClusterCenterIds())) {
                        continue;
                    }

                    $newClusters = $this->generateNewEmptyClustersWithSwapedCenter($clusters, $cluster, $booking);
                    $this->assignBookingsToClusters($newClusters, $filters);
                    $newTotalCosts = $newClusters->getTotalCosts();
                    if ($newTotalCosts < $bestTotalCosts) {
                        $bestTotalCosts = $newTotalCosts;
                        $bestClusters = $newClusters;
                    }
                }
            }
            $delta = $currentTotalCosts - $bestTotalCosts;
            $clusters = $bestClusters;
            $currentTotalCosts = $bestTotalCosts;
            echo "\nCurrentTotalCosts: {$currentTotalCosts}";
            echo "\nBestTotalCosts: {$bestTotalCosts}";
            echo "\nDelta: {$delta}\n-------------------------------------\n";
        }
        echo "done!\n";
        $this->storeStateDone($clusters, $iteration);
        return $clusters;
    }

    /**
     * Gets a Clusters object with $k clusters inside with no Associates added yet.
     * @param int $k Number of clusters.
     * @param Filters|null $filters Filters to apply.
     * @return Clusters Clusters object with $k clusters inside.
     */
    private function getInitialEmptyClusters(int $k, Filters $filters = null): Clusters
    {
        if ($k > $this->bookingsCount) {
            $k = $this->bookingsCount;
        }

        $clusters = new Clusters();
        $clusterCenterIndices = [];
        for ($i = 0; $i < $k; $i++) {
            $max = $this->bookingsCountCap && $this->bookingsCountCap < $this->bookingsCount
                ? $this->bookingsCountCap - 1
                : $this->bookingsCount - 1;
            $clusterCenterIndex = $this->random->generate(100);
            echo "Center index: {$clusterCenterIndex}\n";

            // If index is already set, rerun generation.
            if (in_array($clusterCenterIndex, $clusterCenterIndices)) {
                $i--;
            } else {
                $clusterCenterIndices[] = $clusterCenterIndex;
                $rawBooking = $this->redis->hGetAll($clusterCenterIndex);
                var_dump($rawBooking);
                $booking = $this->bookingsProvider->getBooking($rawBooking);
                $clusters->addCluster(new Cluster($booking));
            }
        }
        return $clusters;
    }

    /**
     * Assignes all bookings from the bookingsProvider to the $clusters.
     * @param Clusters $clusters Clusters to add the bookings to.
     * @param Filters|null $filters Filters to apply.
     */
    private function assignBookingsToClusters(Clusters $clusters, Filters $filters = null)
    {
        $clusterCenterIds = $clusters->getClusterCenterIds();

        $offset = 0;
        $batchSize = 100;
        while (!$this->bookingsProvider->hasEndBeenReached()) {
            if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                break;
            }
            $offset += $batchSize;

            $bookings = $this->bookingsProvider->getSubset($batchSize, $filters);
            $this->bookingsCount += count($bookings);
            foreach ($bookings as $booking) {
                if (in_array($booking->getId(), $clusterCenterIds)) {
                    continue;
                }
                $this->assignBookingToCluster($clusters, $booking);
            }
        }
        $this->bookingsProvider->rewind();
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
     * @return Clusters New Clusters object with one Cluster replaced.
     */
    private function generateNewEmptyClustersWithSwapedCenter(Clusters $oldClusters, Cluster $clusterToReplace, Booking $newCenter)
    {
        $newClusters = new Clusters();
        foreach ($oldClusters->getClusters() as $oldCluster) {
            if ($oldCluster->getCenter()->getId() != $clusterToReplace->getCenter()->getId()) {
                $newClusters->addCluster(new Cluster($oldCluster->getCenter()));
            }
        }
        $newClusters->addCluster(new Cluster($newCenter));
        return $newClusters;
    }

    private function storeState(Clusters $clusters, $iteration)
    {
        $this->progress->storeState($this->startTime, $this->bookingsCount, $clusters, $iteration);
    }

    private function storeStateDone($clusters, $iteration)
    {
        $this->progress->storeState($this->startTime, $this->bookingsCount, $clusters, $iteration, true);
    }
}