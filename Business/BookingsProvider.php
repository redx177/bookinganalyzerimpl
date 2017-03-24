<?php
class BookingsProvider {
    private $csvIterator;
    private $dataTypeClusterer;
    private $idField;
    private $atLeastFilterFields;

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
        $this->atLeastFilterFields = $config->get('atLeastFilterFields');
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
        $currentIndex = 0;

        // Store the last bookings in case $from is higher than the total count of bookings
        $bookingsQueue = new SplQueue();
        for ($i = 0; $i < $from+$count;) {
            // If end of bookings have been reached, exit for loop.
            if (!$this->csvIterator->valid()) {
                break;
            }

            if (($i-$from) % $count === 0) {
                $bookingsQueue = new SplQueue();
            }

            $rawBooking = $this->csvIterator->current();
            $booking = $this->getBooking($rawBooking);

            // Check if the current $booking matches the provided filters.
            if (!$this->applyFilters($booking, $filters)) {
                $this->csvIterator->next();
                $currentIndex++;
                continue;
            }

            // Check if queue limit has been reached.
            if ($bookingsQueue->count() == $count) {
                $bookingsQueue->dequeue();
            }

            $bookingsQueue->enqueue(['line' => $currentIndex, 'booking' => $booking]);
            $this->csvIterator->next();
            $currentIndex++;
            $i++;
        }

        $data = [];
        $bookingsQueueCount = $bookingsQueue->count();
        for ($i = 0; $i < $bookingsQueueCount; $i++) {
            $lineNumberAndBooking = $bookingsQueue->dequeue();
            $data[$lineNumberAndBooking['line']] = $lineNumberAndBooking['booking'];
        }

        return $data;
    }

//    /**
//     * Gets the total item count.
//     * @return int
//     */
//    public function getItemCount()
//    {
//        $itemCount = 0;
//        for ($this->csvIterator->rewind();$this->csvIterator->valid();$this->csvIterator->next()) {
//            $itemCount++;
//        }
//        return $itemCount;
//    }

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
            if (!$fieldValue) {
                continue;
            }
            if (in_array($fieldName, $this->atLeastFilterFields)) {
                if ($bookingFields[$fieldName] < $fieldValue) {
                    return false;
                }
                continue;
            }
            if ($bookingFields[$fieldName] != $fieldValue) {
                return false;
            }
        }
        return true;
    }
}