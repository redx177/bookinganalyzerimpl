<?php

class KPrototypeCluster implements Cluster
{
    private $totalCosts;

    /**
     * @var Booking
     */
    private $center;

    /**
     * @var DistanceClusterPoint[]
     */
    private $clusterPoints = [];

    /**
     * @var Histograms
     */
    private $histograms;

    public function __construct(Booking $booking = null)
    {
        $this->center = $booking;
        $this->totalCosts = 0;
    }

    public function addClusterPoint(DistanceClusterPoint $clusterPoint)
    {
        $this->clusterPoints[$clusterPoint->getId()] = $clusterPoint;
        $this->totalCosts += $clusterPoint->getDistance();
    }

    public function removeClusterPoint(DistanceClusterPoint $clusterPoint)
    {
        unset($this->clusterPoints[$clusterPoint->getId()]);
    }

    /**
     * @return DistanceClusterPoint[]
     */
    public function getClusterPoints(): array
    {
        return $this->clusterPoints;
    }

    public function getCenter(): Booking
    {
        return $this->center;
    }

    public function getTotalCosts(): int
    {
        return $this->totalCosts;
    }

    public function setHistograms(Histograms $histograms)
    {
        $this->histograms = $histograms;
    }
}