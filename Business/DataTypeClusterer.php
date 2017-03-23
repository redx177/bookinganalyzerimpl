<?php

class DataTypeClusterer
{
    private $integerFields;
    private $booleanFields;
    private $floatFields;
    private $stringFields;
    private $priceFields;
    private $distanceFields;

    /**
     * DataTypeClusterer constructor.
     * @param ConfigProvider $config Configuration provider.
     */
    public function __construct(ConfigProvider $config)
    {
        $this->integerFields = $config->get('integerFields');
        $this->booleanFields = $config->get('booleanFields');
        $this->floatFields = $config->get('floatFields');
        $this->stringFields = $config->get('stringFields');
        $this->priceFields = $config->get('priceFields');
        $this->distanceFields = $config->get('distanceFields');
    }

    public function get($rawData)
    {
        $floatFields = [];
        $booleanFields = [];
        $integerFields = [];
        $stringFields = [];
        $priceFields = [];
        $distanceFields = [];
        foreach ($this->floatFields as $fieldName) {
            if (array_key_exists($fieldName, $rawData)) {
                $floatFields[$fieldName] = (float)$rawData[$fieldName];
            }
        }
        foreach ($this->booleanFields as $fieldName) {
            if (array_key_exists($fieldName, $rawData)) {
                $booleanFields[$fieldName] = $rawData[$fieldName] !== '0.0' && $rawData[$fieldName];
            }
        }
        foreach ($this->integerFields as $fieldName) {
            if (array_key_exists($fieldName, $rawData)) {
                if (is_array($rawData[$fieldName])) {
                    $integerFields[$fieldName] = [];
                    foreach ($rawData[$fieldName] as $value) {
                        array_push($integerFields[$fieldName], (int)$value);
                    }
                } else {
                    $integerFields[$fieldName] = (int)$rawData[$fieldName];
                }
            }
        }
        foreach ($this->stringFields as $fieldName) {
            if (array_key_exists($fieldName, $rawData)) {
                $stringFields[$fieldName] = $rawData[$fieldName];
            }
        }
        foreach ($this->priceFields as $fieldName) {
            if (array_key_exists($fieldName, $rawData)) {
                $priceFields[$fieldName] = $this->getPrice($rawData[$fieldName]);
            }
        }
        foreach ($this->distanceFields as $fieldName) {
            if (array_key_exists($fieldName, $rawData)) {
                $distanceFields[$fieldName] = $this->getDistance($rawData[$fieldName]);
            }
        }
        return new DataTypeCluster($integerFields, $booleanFields, $floatFields, $stringFields, $priceFields, $distanceFields);
    }

    private function getPrice($value)
    {
        if (strtolower($value) == 'budget') {
            return Price::Budget;
        } elseif (strtolower($value) == 'luxury') {
            return Price::Luxury;
        } else {
            return Price::Empty;
        }
    }

    private function getDistance($value)
    {
        if (strtolower($value) == 'close') {
            return Distance::Close;
        } else {
            return Distance::Empty;
        }
    }
}