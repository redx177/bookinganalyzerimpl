<?php

class DistanceClusterPoint extends ClusterPoint
{
    /**
     * @var float
     */
    private $distance;

    public function __construct(Booking $booking, float $distance)
    {
        parent::__construct($booking);
        $this->distance = $distance;
    }

    public function getDistance(): float
    {
        return $this->distance;
    }
}