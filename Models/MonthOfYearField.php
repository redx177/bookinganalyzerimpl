<?php

class MonthOfYearField implements Field
{
    private $name;
    private $value;

    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
        if (!is_array($value)) {
            $date = DateTime::createFromFormat('!n', $value);
            $this->displayValue = $date->format('M');
        }
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
        return $this->value !== null && $this->value !== [];
    }

    public function getType()
    {
        return self::Type();
    }

    public static function Type()
    {
        return MonthOfYearField::class;
    }
}