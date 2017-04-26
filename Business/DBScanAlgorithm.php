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
        $this->minPoints = $dbscanConfig['minPoints'];

        $this->bookingsCount = $this->bookingDataIterator->count();
        if ($this->bookingsCountCap) {
            $this->bookingsCount = $this->bookingsCountCap;
        }
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
        $noise = [];
        $clusters = [];
        echo 1;
        foreach ($this->bookingDataIterator as $booking) {
            echo 2;

            // If the point is already visited, skip it.
            if ($this->isVisited($booking, $visitedIds)) {
                continue;
            }

            $visitedIds[] = $booking->getId();
            $neighbours = $this->getNeighbours($booking);
            if (count($neighbours) >= $this->minPoints) {
                echo 3;
                $cluster = [$booking];
                $idsInACluster[] = $booking->getId();
                $neighboursCount = count($neighbours);
                for ($i = 0; $i < $neighboursCount; $i++) {
                    echo 4;
                    $neighbour = $neighbours[$i];
                    if (!$this->isVisited($neighbour, $visitedIds)) {
                        echo 5;
                        $visitedIds[] = $neighbour->getId();
                        $neighbourCandidates = $this->getNeighbours($neighbour);
                        if (count($neighbours) >= $this->minPoints) {
                            echo 6;
                            $neighbours = array_merge($neighbours, $neighbourCandidates);
                            $neighboursCount += count($neighbourCandidates);
                        }
                        if (!in_array($neighbour->getId(), $idsInACluster)) {
                            echo 7;
                            $idsInACluster[] = $neighbour->getId();
                            $cluster[] = $neighbour;
                        }
                    }

                    if ($this->bookingsCountCap && count($noise) + count($idsInACluster) >= $this->bookingsCountCap) {
                        echo 8;
                        break 2;
                    }
                }
                $clusters[] = $cluster;
            } else {
                echo 9;
                $noise[] = $booking;
            }

            if ($this->bookingsCountCap && count($noise) + count($idsInACluster) >= $this->bookingsCountCap) {
                break;
            }
        }
        return new DBScanResult($this->createClusters($clusters), $noise);
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
}