<?php

class Associate
{
    /**
     * @var Booking
     */
    private $booking;
    /**
     * @var float
     */
    private $distance;

    /**
     * Associate constructor.
     * @param Booking $booking
     * @param float $distance
     */
    public function __construct($booking, $distance)
    {
        $this->booking = $booking;
        $this->distance = $distance;
    }

    /**
     * @return Booking
     */
    public function getBooking(): Booking
    {
        return $this->booking;
    }

    /**
     * @return float
     */
    public function getDistance(): float
    {
        return $this->distance;
    }
}