<?php

class HistogramBin
{
    private $fields;
    private $count;
    private $total;

    public function __construct($fields, $count, $total)
    {
        $this->fields = $fields;
        $this->count = $count;
        $this->toxtal = $total;
    }
    public function getFields()
    {
        return $this->fields;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getPercentage()
    {
        return $this->count * 100 / $this->total;
    }
}