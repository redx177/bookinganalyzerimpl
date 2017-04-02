<?php

class StringField implements Field
{
    private $name;
    private $value;

    public function __construct(string $name, string $value)
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
        return $this->value != '';
    }

    public static function getType()
    {
        return string::class;
    }
}