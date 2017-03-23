<?php
class DataProvider {
    private $csvIterator;

    /**
     * Integer typed field names.
     * @var array
     */
    private $integerFields;

    /**
     * Boolean typed field names.
     * @var array
     */
    private $booleanFields;

    /**
     * Float typed field names.
     * @var array
     */
    private $floatFields;

    /**
     * String typed field names.
     * @var array
     */
    private $stringFields;

    /**
     * Field name for the id field.
     * @var string
     */
    private $idField;

    /**
     * Price field names.
     * @var array
     */
    private $priceFields;

    /**
     * Distance field names.
     * @var array
     */
    private $distanceFields;

    /**
     * DataProvider constructor.
     * @param CsvIterator $csvIterator Iterator to access data.
     * @param ConfigProvider $config Configuration provider.
     */
    public function __construct(CsvIterator $csvIterator, ConfigProvider $config)
    {
        $this->csvIterator = $csvIterator;
        $this->integerFields = $config->get('integerFields');
        $this->booleanFields = $config->get('booleanFields');
        $this->floatFields = $config->get('floatFields');
        $this->stringFields = $config->get('stringFields');
        $this->priceFields = $config->get('priceFields');
        $this->distanceFields = $config->get('distanceFields');
        $this->idField = $config->get('idField');

    }

    /**
     * Gets a subset of data.
     * @param $from Start index to retrieve data from. Count is starting from index 0
     * @param $count Number of elements to get.
     * @return array Requested data. Array of Booking
     */
    public function getSubset($from, $count)
    {
        $this->csvIterator->skip($from);
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            if (!$this->csvIterator->valid()) {
                break;
            }
            $rawBooking = $this->csvIterator->current();
            array_push($data, $this->getBooking($rawBooking));
            $this->csvIterator->next();
        }
        return $data;
    }

    /**
     * Gets the total item count.
     * @return int
     */
    public function getItemCount()
    {
        $itemCount = 0;
        foreach ($this->csvIterator as $value) {
            $itemCount++;
        }
        return $itemCount;
    }

    private function getBooking($rawBooking)
    {
        $floatFields = [];
        $booleanFields = [];
        $integerFields = [];
        $stringFields = [];
        $priceFields = [];
        $distanceFields = [];
        foreach ($this->floatFields as $fieldName) {
            if (array_key_exists($fieldName, $rawBooking)) {
                $floatFields[$fieldName] = (float)$rawBooking[$fieldName];
            }
        }
        foreach ($this->booleanFields as $fieldName) {
            if (array_key_exists($fieldName, $rawBooking)) {
                $booleanFields[$fieldName] = $rawBooking[$fieldName] === '1.0';
            }
        }
        foreach ($this->integerFields as $fieldName) {
            if (array_key_exists($fieldName, $rawBooking)) {
                $integerFields[$fieldName] = (int)$rawBooking[$fieldName];
            }
        }
        foreach ($this->stringFields as $fieldName) {
            if (array_key_exists($fieldName, $rawBooking)) {
                $stringFields[$fieldName] = $rawBooking[$fieldName];
            }
        }
        foreach ($this->priceFields as $fieldName) {
            if (array_key_exists($fieldName, $rawBooking)) {
                $priceFields[$fieldName] = $this->getPrice($rawBooking[$fieldName]);
            }
        }
        foreach ($this->distanceFields as $fieldName) {
            if (array_key_exists($fieldName, $rawBooking)) {
                $distanceFields[$fieldName] = $this->getDistance($rawBooking[$fieldName]);
            }
        }
        $idField = $rawBooking[$this->idField];
        return new Booking($idField, $integerFields, $booleanFields, $floatFields, $stringFields, $priceFields, $distanceFields);
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