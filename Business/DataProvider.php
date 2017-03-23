<?php
class DataProvider {
    private $csvIterator;

    public function __construct(CsvIterator $csvIterator)
    {
        $this->csvIterator = $csvIterator;
    }

    /**
     * Gets a subset of data.
     * @param $from Start index to retrieve data from. Count is starting from index 0
     * @param $count Number of elements to get.
     * @return array Requested data.
     */
    public function getSubset($from, $count)
    {
        $this->csvIterator->skip($from);
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            if (!$this->csvIterator->valid()) {
                break;
            }
            $rawValue = $this->csvIterator->current();
            $typedData = $this->typeData($rawValue);
            array_push($data, $typedData);
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

    private function typeData($rawValue)
    {
        $typedData = [];
        foreach ($rawValue as $key => $value) {
            $typedData[$key] = is_numeric($value) ? $value + 0 : $value;
        }
        return $typedData;
    }
}