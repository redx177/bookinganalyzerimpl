<?php

class StringField implements Field
{
    private $name;
    private $value;

    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = strtolower($value);
        $this->displayValue = $value;
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
        return $this->value != '';
    }

    public function getType()
    {
        return self::Type();
    }

    public static function Type()
    {
        return StringField::class;
    }
}