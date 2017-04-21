<?php

class DistanceField implements Field
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
        return $this->value != Distance::Empty;
    }

    public static function getType()
    {
        return DistanceField::class;
    }
}