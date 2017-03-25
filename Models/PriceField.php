<?php

class PriceField implements Field
{
    private $name;
    private $value;

    public function __construct(string $name, int $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function hasValue()
    {
        return $this->value != Price::Empty;
    }

    public function getType()
    {
        return Price::class;
    }
}