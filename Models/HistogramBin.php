<?php

class HistogramBin
{
    private $field;
    private $value;
    private $count;
    private $total;

    public function __construct($field, $value, $count, $total)
    {
        $this->field = $field;
        $this->value = $value;
        $this->count = $count;
        $this->toxtal = $total;
    }
    public function getField()
    {
        return $this->field;
    }

    public function getValue()
    {
        return $this->value;
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