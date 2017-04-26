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
     * @return KPrototypeResult
     */
    public function run(): KPrototypeResult
    {
        /** @var Booking[] $noise */
        $visitedIds = [];
        $idsInACluster = [];
        $noise = [];
        $clusters = [];
        foreach ($this->bookingDataIterator as $booking) {

            // If the point is already visited, skip it.
            if ($this->isVisited($booking, $visitedIds)) {
                continue;
            }

            $visitedIds[] = $booking->getId();
            $neighbours = $this->getNeighbours($booking);
            if (count($neighbours) >= $this->minPoints) {
                $cluster = [$booking];
                $idsInACluster[] = $booking->getId();
                for ($i = 0; $i < count($neighbours); $i++) {
                    $neighbour = $neighbours[$i];
                    if (!$this->isVisited($neighbour, $visitedIds)) {
                        $visitedIds[] = $neighbour->getId();
                        $neighbourCandidates = $this->getNeighbours($neighbour);
                        if (count($neighbours) >= $this->minPoints) {
                            $neighbours = array_merge($neighbours, $neighbourCandidates);
                            $i += count($neighbours);
                        }
                        if (!in_array($neighbour, $idsInACluster)) {
                            $cluster[] = $neighbour;
                        }
                    }
                }
                $clusters[] = $cluster;
            } else {
                $noise[] = $booking;
            }
        }
        return new DBScanResult($clusters, $noise);
    }

    /**
     * @return Booking[]
     */
    private function getNeighbours(Booking $booking)
    {
        $neighbours = [];
        foreach ($this->bookingDataIterator2 as $possibleNeighbour) {
            if ($this->distance->measure($booking, $possibleNeighbour) < $this->radius) {
                $neighbours[] = $possibleNeighbour;
            }
        }
        $this->bookingDataIterator2->rewind();

        return $neighbours;
    }

    protected function isVisited(Booking $booking, $visitedIds): bool
    {
        return in_array($booking->getId(), $visitedIds);
    }
}