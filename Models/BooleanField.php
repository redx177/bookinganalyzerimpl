<?php

class BooleanField implements Field
{
    private $name;
    private $value;

    public function __construct(string $name, bool $value)
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
        return $this->value ? '1' : '0';
    }

    public function hasValue()
    {
        return $this->value;
    }

    public function getType()
    {
        return self::Type();
    }

    public static function Type()
    {
        return BooleanField::class;
    }
}