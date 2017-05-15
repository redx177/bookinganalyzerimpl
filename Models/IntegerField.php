<?php

class IntegerField implements Field
{
    private $name;
    private $value;

    public function __construct(string $name, $value)
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

    public function getDisplayValue()
    {
        return $this->value;
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
        return IntegerField::class;
    }
}