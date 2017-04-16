<?php

class Cluster
{
    /**
     * @var Booking
     */
    private $center;

    /**
     * @var Associate[]
     */
    private $associates = [];
    private $totalCosts;

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
}