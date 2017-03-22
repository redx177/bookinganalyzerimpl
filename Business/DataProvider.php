<?php
class DataProvider {
    private $csvIterator;

    public function __construct(CsvIterator $csvIterator)
    {
        $this->csvIterator = $csvIterator;
    }

    /**
     * Gets a subset of data.
     * @param $from Start index to retrieve data from.
     * @param $count Number of elements to get.
     * @return array Requested data.
     */
    public function getSubset($from, $count)
    {
        $this->csvIterator->skip($from);
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            array_push($data, $this->csvIterator->current());
            $this->csvIterator->next();
        }
        return $data;
    }
}