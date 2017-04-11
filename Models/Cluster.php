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
    private $totalError;

    public function __construct(Booking $booking)
    {
        $this->center = $booking;
        $this->totalError = 0;
    }

    public function addAssociate(Associate $associate) {
        $this->associates[] = $associate;
        $this->totalError += $associate->getDistance();
    }

    public function getAssociates(): array
    {
        return $this->associates;
    }

    public function getCenter(): Booking
    {
        return $this->center;
    }

    public function getTotalError(): int
    {
        return $this->totalError;
    }
}