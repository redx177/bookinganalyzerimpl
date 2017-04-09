<?php

class Prototype
{
    /**
     * @var Booking
     */
    private $prototypeBooking;

    /**
     * @var Booking[]
     */
    private $associatedBookings = [];
    /**
     * Prototype constructor.
     * @param $booking
     */
    public function __construct($booking)
    {
        $this->prototypeBooking = $booking;
    }

    /**
     * Adds a booking to the list of associated bookings of this prototype.
     * @param Booking $booking Booking to add to the list of associated bookings.
     */
    public function addAssociatedBooking(Booking $booking) {
        $this->associatedBookings[] = $booking;
    }

    /**
     * Gets the associated bookings of this prototype.
     * @return Booking[] Associated bookings of this prototype.
     */
    public function getAssociatedBookings(): array
    {
        return $this->associatedBookings;
    }

    /**
     * Gets the booking of the prototype.
     * @return Booking Booking of the prototype.
     */
    public function getPrototypeBooking(): Booking
    {
        return $this->prototypeBooking;
    }
}