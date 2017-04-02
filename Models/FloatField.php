<?php

class FloatField implements Field
{
    private $name;
    private $value;

    public function __construct(string $name, float $value)
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
        return $this->value > 0;
    }

    public static function getType()
    {
        return float::class;
    }
}