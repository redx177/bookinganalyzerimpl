<?php

class DataTypeCluster
{
    private $integerFields;
    private $booleanFields;
    private $floatFields;
    private $stringFields;
    private $priceFields;
    private $distanceFields;
    private $dayOfWeekFields;
    private $monthOfYearFields;

    /**
     * DataTypeCluster constructor.
     * @param array $integerFields Integer typed fields.
     * @param array $booleanFields Boolean typed fields.
     * @param array $floatFields Float typed fields.
     * @param array $stringFields String typed fields.
     * @param array $priceFields Price fields.
     * @param array $distanceFields Distance fields.
     * @param array $dayOfWeekFields Day of week fields.
     * @param array $monthOfYearFields Month of year fields
     */
    public function __construct(array $integerFields, array $booleanFields, array $floatFields,
                                array $stringFields, array $priceFields, array $distanceFields,
                                array $dayOfWeekFields, array $monthOfYearFields)
    {
        $this->integerFields = $integerFields;
        $this->booleanFields = $booleanFields;
        $this->floatFields = $floatFields;
        $this->stringFields = $stringFields;
        $this->priceFields = $priceFields;
        $this->distanceFields = $distanceFields;
        $this->dayOfWeekFields = $dayOfWeekFields;
        $this->monthOfYearFields = $monthOfYearFields;
    }

    public function getIntegerFields(): array
    {
        return $this->integerFields;
    }

    public function getBooleanFields(): array
    {
        return $this->booleanFields;
    }

    public function getFloatFields(): array
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

    public function getDayOfWeekFields(): array
    {
        return $this->dayOfWeekFields;
    }

    public function getMonthOfYearFields(): array
    {
        return $this->monthOfYearFields;
    }
}