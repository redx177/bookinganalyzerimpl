<?php

interface BookingDataIterator extends Iterator
{
    /**
     * Skips a given amount of lines.
     * @param $count Number of line to skip.
     */
    public function skip($count);

    /**
     * Gets the total amount of bookings.
     * @return int Total amount of bookings
     */
    public function count(): int;
}