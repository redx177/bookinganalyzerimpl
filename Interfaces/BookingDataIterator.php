<?php

interface BookingDataIterator extends Iterator
{
    /**
     * Skips a given amount of lines.
     * @param $count Number of line to skip.
     */
    public function skip($count);
}