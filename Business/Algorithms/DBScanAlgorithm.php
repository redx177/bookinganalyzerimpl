<?php

class DBScanAlgorithm
{
    private $bookingsCount;
    private $bookingsCountCap;
    private $radius;
    private $minPoints;

    /**
     * @var DistanceMeasurement
     */
    private $distance;
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

    public function __construct(
        ConfigProvider $config,
        DistanceMeasurement $distance,
        Random $random,
        Redis $redis,
        BookingDataIterator $bookingDataIterator,
        BookingDataIterator $bookingDataIterator2,
        ClusteringProgress $progress)
    {
        $this->distance = $distance;
        $this->bookingsCountCap = $config->get('bookingsCountCap');
        $this->bookingDataIterator = $bookingDataIterator;
        $this->bookingDataIterator2 = $bookingDataIterator2;
        $this->progress = $progress;

        $dbscanConfig = $config->get('dbscan');
        $this->radius = $dbscanConfig['radius'];

        $this->bookingsCount = $this->bookingDataIterator->count();
        if ($this->bookingsCountCap) {
            $this->bookingsCount = $this->bookingsCountCap;
        }
        $this->minPoints = $this->bookingsCount * $dbscanConfig['minPoints'];
    }

    /**
     * Runs the DBSCAN clustering algorithm.
     * @return DBScanResult
     */
    public function run(): DBScanResult
    {
        /** @var Booking[] $noise */
        $visitedIds = [];
        $idsInACluster = [];
        $clusters = new DBScanResult();
        $j = 0;
        foreach ($this->bookingDataIterator as $booking) {
            $j++;
            echo "1\n";

            // If the point is already visited, skip it.
            if ($this->isVisited($booking, $visitedIds)) {
                continue;
            }

            $visitedIds[] = $booking->getId();
            $neighbours = $this->getNeighbours($booking);
            if (count($neighbours) >= $this->minPoints) {
                $cluster = new DBScanCluster();
                $cluster->addClusterPoint(new ClusterPoint($booking));
                $clusters->addCluster($cluster);
                $idsInACluster[] = $booking->getId();
                $neighboursCount = count($neighbours);
                for ($i = 0; $i < $neighboursCount; $i++) {
                    if ($i % 50 == 0) {
                        $this->storeState($clusters, 0);
                    }
                    echo 2;
                    $neighbour = $neighbours[$i];
                    if (!$this->isVisited($neighbour, $visitedIds)) {
                        $visitedIds[] = $neighbour->getId();
                        $neighbourCandidates = $this->getNeighbours($neighbour);
                        if (count($neighbourCandidates) >= $this->minPoints) {
                            echo 3;
                            $neighbours = array_merge($neighbours, $neighbourCandidates);
                            $neighboursCount += count($neighbourCandidates);
                        }
                    }
                    if (!in_array($neighbour->getId(), $idsInACluster)) {
                        $idsInACluster[] = $neighbour->getId();
                        $cluster->addClusterPoint(new ClusterPoint($neighbour));
                    }

                    if ($this->bookingsCountCap && count($clusters->getNoise()) + count($idsInACluster) >= $this->bookingsCountCap) {
                        break 2;
                    }
                }
            } else {
                $clusters->addNoisePoint(new ClusterPoint($booking));
            }
            if ($j % 50 == 0) {
                $this->storeState($clusters, 0);
            }

            if ($this->bookingsCountCap && count($clusters->getNoise()) + count($idsInACluster) >= $this->bookingsCountCap) {
                break;
            }
        }
        $this->storeState($clusters, 1);
        return $clusters;
        //return new DBScanResult($this->createClusters($clusters), $noise);
    }

    /**
     * @return Booking[]
     */
    private function getNeighbours(Booking $booking)
    {
        $neighbours = [];
        $i = 0;
        foreach ($this->bookingDataIterator2 as $possibleNeighbour) {
            if ($booking->getId() === $possibleNeighbour->getId()) {
                continue;
            }

            if ($this->distance->measure($booking, $possibleNeighbour) < $this->radius) {
                $neighbours[] = $possibleNeighbour;
            }
            $i++;
            if ($i >= $this->bookingsCountCap) {
                break;
            }
        }
        $this->bookingDataIterator2->rewind();

        return $neighbours;
    }

    protected function isVisited(Booking $booking, $visitedIds): bool
    {
        return in_array($booking->getId(), $visitedIds);
    }

    private function createClusters($clustersArray)
    {
        /** @var Cluster[] $clusters */
        /** @var ClusterPoint[] $points */
        $clusters = [];
        foreach ($clustersArray as $cluster) {
            $points = [];
            foreach ($cluster as $point) {
                $points[] = new ClusterPoint($point);
            }
            $clusters[] = new DBScanCluster($points);
        }
        return $clusters;
    }

    /**
     * @param int $status 0 = Data caching done. 1 = Clustering done. 2 = Apriori done.
     */
    private function storeState(DBScanResult $clusters, int $status)
    {
        echo "store state. Status: {$status}\n";
        $this->progress->storeState($this->bookingsCount, $clusters, $status);
    }
}