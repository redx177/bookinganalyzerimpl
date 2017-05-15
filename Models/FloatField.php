<?php

class FloatField implements Field
{
    private $name;
    private $value;
    private $displayValue;

    public function __construct(string $name, float $value, float $displayValue)
    {
        $this->name = $name;
        $this->value = $value;
        $this->displayValue = $displayValue;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDisplayValue()
    {
        return $this->displayValue;
    }

    public function hasValue()
    {
        return $this->value > 0;
    }

    public function getType()
    {
        return self::Type();
    }

    public static function Type()
    {
        return FloatField::class;
    }
}