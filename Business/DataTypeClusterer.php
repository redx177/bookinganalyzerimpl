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
        $this->floatFieldsBoundaries = $config->get('floatFieldsBoundaries');
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
                $floatValue = (float)$rawData[$fieldName];
                $floatFields[$fieldName] = new FloatField($fieldName, $floatValue, $this->getDisplayValue($floatValue, $this->floatFieldsBoundaries[$fieldName]));
            }
        }
        foreach ($this->booleanFields as $fieldName) {
            if (array_key_exists($fieldName, $rawData)) {
                $boolValue = $rawData[$fieldName] !== '0.0' && $rawData[$fieldName];
                $booleanFields[$fieldName] = new BooleanField($fieldName, $boolValue);
            }
        }
        foreach ($this->integerFields as $fieldName) {
            if (array_key_exists($fieldName, $rawData)) {
                if (is_array($rawData[$fieldName])) {
                    $intValues = [];
                    foreach ($rawData[$fieldName] as $value) {
                        $intValues[] = (int)$value;
                    }
                    $integerFields[$fieldName] = new IntegerField($fieldName, $intValues);
                } else {
                    $intValue = (int)$rawData[$fieldName];
                    $integerFields[$fieldName] = new IntegerField($fieldName, $intValue);
                }
            }
        }
        foreach ($this->stringFields as $fieldName) {
            if (array_key_exists($fieldName, $rawData)) {
                $stringValue = $rawData[$fieldName];
                $stringFields[$fieldName] = new StringField($fieldName, $stringValue);
            }
        }
        foreach ($this->priceFields as $fieldName) {
            if (array_key_exists($fieldName, $rawData)) {
                $priceValue = $this->getPrice($rawData[$fieldName]);
                $priceFields[$fieldName] = new PriceField($fieldName, $priceValue);
            }
        }
        foreach ($this->distanceFields as $fieldName) {
            if (array_key_exists($fieldName, $rawData)) {
                $distanceValue = $this->getDistance($rawData[$fieldName]);
                $distanceFields[$fieldName] = new DistanceField($fieldName, $distanceValue);
            }
        }
        return new DataTypeCluster($integerFields, $booleanFields, $floatFields, $stringFields, $priceFields, $distanceFields);
    }

    private function getPrice($value)
    {
        $lowerCaseValue = strtolower($value);
        if ($lowerCaseValue == 'budget') {
            return Price::Budget;
        } elseif ($lowerCaseValue == 'luxury') {
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

    private function getDisplayValue($value, $config)
    {
        $displayValue = $value / $config['increment'];
        if ($config['lowest'] === 0) {
            $displayValue += 1;
        }
        return round($displayValue);
    }
}