<?php

class ClusterPoint
{
    /**
     * @var int
     */
    protected $bookingId;

    public function __construct(Booking $booking)
    {
        $this->bookingId = $booking->getId();
    }

    public function getId(): int
    {
        return $this->bookingId;
    }
}