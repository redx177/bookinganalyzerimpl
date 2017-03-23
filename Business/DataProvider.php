<?php
class DataProvider {
    private $csvIterator;
    /**
     * @var DataTypeClusterer
     */
    private $dataTypeClusterer;

    /**
     * DataProvider constructor.
     * @param CsvIterator $csvIterator Iterator to access data.
     * @param DataTypeClusterer $dataTypeClusterer Data type clusterer to group raw booking data.
     * @internal param ConfigProvider $config Configuration provider.
     */
    public function __construct(CsvIterator $csvIterator, DataTypeClusterer $dataTypeClusterer)
    {
        $this->csvIterator = $csvIterator;
        $this->dataTypeClusterer = $dataTypeClusterer;
    }

    /**
     * Gets a subset of data.
     * @param $from Start index to retrieve data from. Count is starting from index 0
     * @param $count Number of elements to get.
     * @return array Requested data. Array of Booking
     */
    public function getSubset($from, $count)
    {
        $this->csvIterator->rewind();
        $this->csvIterator->skip($from);
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            if (!$this->csvIterator->valid()) {
                break;
            }
            $rawBooking = $this->csvIterator->current();
            $dataTypeCluster = $this->dataTypeClusterer->get($rawBooking);
            array_push($data, new Booking($dataTypeCluster));
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
        for ($this->csvIterator->rewind();$this->csvIterator->valid();$this->csvIterator->next()) {
            $itemCount++;
        }
        return $itemCount;
    }
}