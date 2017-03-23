<?php
class Booking
{
    private $id;
    private $integerFields;
    private $booleanFields;
    private $floatFields;
    private $stringFields;
    private $priceFields;
    private $distanceFields;

    /**
     * Booking constructor.
     * @param $id int ID of the booking
     * @param $integerFields array Integer typed fields.
     * @param $booleanFields array Booleand typed fields.
     * @param $floatFields array Float typed fields.
     * @param $stringFields array String typed fields.
     * @param $priceFields array Price fields.
     * @param $distanceFields array Distance fields.
     */
    public function __construct($id, $integerFields, $booleanFields, $floatFields, $stringFields, $priceFields, $distanceFields)
    {
        $this->id = $id;
        $this->integerFields = $integerFields;
        $this->booleanFields = $booleanFields;
        $this->floatFields = $floatFields;
        $this->stringFields = $stringFields;
        $this->priceFields = $priceFields;
        $this->distanceFields = $distanceFields;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getIntegerFields() : array
    {
        return $this->integerFields;
    }

    public function getBooleanFields() : array
    {
        return $this->booleanFields;
    }

    public function getFloatFields() : array
    {
        return $this->floatFields;
    }

    public function getStringFields(): array
    {
        return $this->stringFields;
    }

    public function getPriceFields(): array
    {
        return $this->priceFields;
    }

    public function getDistanceFields(): array
    {
        return $this->distanceFields;
    }
}