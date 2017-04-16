<?php

class Associate
{
    /**
     * @var int
     */
    private $bookingId;
    /**
     * @var float
     */
    private $distance;

    public function __construct(Booking $booking, float $distance)
    {
        $this->bookingId = $booking->getId();
        $this->distance = $distance;
    }

    public function getDistance(): float
    {
        return $this->distance;
    }

    public function getId(): int
    {
        return $this->bookingId;
    }
}