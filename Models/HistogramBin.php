<?php

class HistogramBin
{
    private $fields;
    private $count;
    private $total;

    public function __construct(array $fields, int $count, int $total)
    {
        $this->fields = $fields;
        $this->count = $count;
        $this->total = $total;
    }
    public function getFields() : array
    {
        return $this->fields;
    }

    public function getCount() : int
    {
        return $this->count;
    }

    public function getTotal() : int
    {
        return $this->total;
    }

    public function getPercentage() : float
    {
        return $this->count * 100 / $this->total;
    }
}