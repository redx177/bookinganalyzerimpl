<?php
class BookingsProvider {
    private $csvIterator;
    private $dataTypeClusterer;
    private $idField;

    /**
     * DataProvider constructor.
     * @param CsvIterator $csvIterator Iterator to access data.
     * @param DataTypeClusterer $dataTypeClusterer Data type clusterer to group raw booking data.
     * @param ConfigProvider $config Configuration provider.
     */
    public function __construct(CsvIterator $csvIterator, DataTypeClusterer $dataTypeClusterer, ConfigProvider $config)
    {
        $this->csvIterator = $csvIterator;
        $this->dataTypeClusterer = $dataTypeClusterer;
        $this->idField = $config->get('idField');
    }

    /**
     * Gets a subset of data.
     * @param $from Start index to retrieve data from. Count is starting from index 0
     * @param $count Number of elements to get.
     * @param Filters|null $filters Filters to apply to the booking data
     * @return array Requested data. Array of Booking
     */
    public function getSubset($from, $count, Filters $filters = null)
    {
        $this->csvIterator->rewind();
        $this->csvIterator->skip($from);
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            if (!$this->csvIterator->valid()) {
                $this->csvIterator->next();
                continue;
            }

            $rawBooking = $this->csvIterator->current();
            $booking = $this->getBooking($rawBooking);

            if (!$this->applyFilters($booking, $filters)) {
                $this->csvIterator->next();
                continue;
            }

            array_push($data, $booking);
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

    private function getBooking(array $rawBooking): Booking
    {
        $dataTypeCluster = $this->dataTypeClusterer->get($rawBooking);
        $id = $rawBooking[$this->idField];
        $booking = new Booking($id, $dataTypeCluster);
        return $booking;
    }

    private function applyFilters(Booking $booking, Filters $filters = null)
    {
        if ($filters === null) {
            return true;
        }
        if (!$this->applyTypedFilters($booking->getIntegerFields(), $filters->getIntegerFields())) {
            return false;
        }
        if (!$this->applyTypedFilters($booking->getBooleanFields(), $filters->getBooleanFields())) {
            return false;
        }
        if (!$this->applyTypedFilters($booking->getFloatFields(), $filters->getFloatFields())) {
            return false;
        }
        if (!$this->applyTypedFilters($booking->getStringFields(), $filters->getStringFields())) {
            return false;
        }
        if (!$this->applyTypedFilters($booking->getDistanceFields(), $filters->getDistanceFields())) {
            return false;
        }
        if (!$this->applyTypedFilters($booking->getPriceFields(), $filters->getPriceFields())) {
            return false;
        }
        return true;
    }

    private function applyTypedFilters(array $bookingFields, array $filterFields) {
        foreach ($filterFields as $fieldName => $fieldValue) {
            if ($bookingFields[$fieldName] != $fieldValue) {
                return false;
            }
        }
        return true;
    }
}