<?php

class BookingDataIterator implements DataIterator
{
    /**
     * @var DataIterator
     */
    private $dataIterator;
    /**
     * @var BookingBuilder
     */
    private $bookingBuilder;

    public function __construct(DataIterator $dataIterator, BookingBuilder $bookingBuilder)
    {
        $this->dataIterator = $dataIterator;
        $this->bookingBuilder = $bookingBuilder;
    }

    public function skip($count)
    {
        $this->dataIterator->skip($count);
    }

    public function count(): int
    {
        return $this->dataIterator->count();
    }

    public function current()
    {
        return $this->bookingBuilder->fromRawData($this->dataIterator->current());
    }

    public function next()
    {
        $this->dataIterator->next();
    }

    public function key()
    {
        return $this->dataIterator->key();
    }

    public function valid()
    {
        return $this->dataIterator->valid();
    }

    public function rewind()
    {
        $this->dataIterator->rewind();
    }
}