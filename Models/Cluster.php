<?php

class Cluster
{
    private $totalCosts;

    /**
     * @var Booking
     */
    private $center;

    /**
     * @var Associate[]
     */
    private $associates = [];

    /**
     * @var Histograms
     */
    private $histograms;

    public function __construct(Booking $booking)
    {
        $this->center = $booking;
        $this->totalCosts = 0;
    }

    public function addAssociate(Associate $associate)
    {
        $this->associates[$associate->getId()] = $associate;
        $this->totalCosts += $associate->getDistance();
    }

    public function removeAssociate(Associate $associate)
    {
        unset($this->associates[$associate->getId()]);
    }

    /**
     * @return Associate[]
     */
    public function getAssociates(): array
    {
        return $this->associates;
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