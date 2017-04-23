<?php

class DataIteratorAdapter implements DataIterator
{
    /**
     * @var DataIterator
     */
    private $bookingDataIterator;
    /**
     * @var BookingBuilder
     */
    private $bookingBuilder;

    public function __construct(DataIterator $bookingDataIterator, BookingBuilder $bookingBuilder)
    {
        $this->bookingDataIterator = $bookingDataIterator;
        $this->bookingBuilder = $bookingBuilder;
    }

    public function skip($count)
    {
        $this->bookingDataIterator->skip($count);
    }

    public function count(): int
    {
        return $this->bookingDataIterator->count();
    }

    public function current()
    {
        return $this->bookingBuilder->fromRawData($this->bookingDataIterator->current());
    }

    public function next()
    {
        $this->bookingDataIterator->next();
    }

    public function key()
    {
        return $this->bookingDataIterator->key();
    }

    public function valid()
    {
        return $this->bookingDataIterator->valid();
    }

    public function rewind()
    {
        $this->bookingDataIterator->rewind();
    }
}