<?php

class Filter
{
    /**
     * @var Field
     */
    private $field;

    public function __construct(Field $field, string $type)
    {
        $this->field = $field;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->field->getName();
    }

    public function getValue()
    {
        return $this->field->getValue();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function hasValue(): bool
    {
        return $this->field->hasValue();
    }
}