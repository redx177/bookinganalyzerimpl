<?php
class BookingsProvider {
    private $bookingDataIterator;
    private $dataTypeClusterer;
    private $idField;
    private $atLeastFilterFields;
    private $nextItem = null;
    private $hasEndBeenReached = false;

    /**
     * @var SplQueue
     */
    private $lastPageItems;

    /**
     * DataProvider constructor.
     * @param BookingDataIterator $bookingDataIterator Iterator to access data.
     * @param DataTypeClusterer $dataTypeClusterer Data type clusterer to group raw booking data.
     * @param ConfigProvider $config Configuration provider.
     */
    public function __construct(BookingDataIterator $bookingDataIterator, DataTypeClusterer $dataTypeClusterer, ConfigProvider $config)
    {
        $this->bookingDataIterator = $bookingDataIterator;
        $this->dataTypeClusterer = $dataTypeClusterer;
        $this->idField = $config->get('idField');
        $this->atLeastFilterFields = $config->get('atLeastFilterFields');
    }

    /**
     * Gets a subset of data.
     * @param int $count Number of elements to get.
     * @param Filters|null $filters Filters to apply to the booking data
     * @param int $from Start index to retrieve data from. Count is starting from index 0
     * @return array Requested data. Array of Booking
     */
    public function getSubset(int $count, Filters $filters = null, int $from = 0)
    {
        $lineNumber = 0;
        if ($this->bookingDataIterator->key() != $from) {
            $lineNumber = $this->rewindAndSkipToFrom($from, $count, $filters);
        }

        $data = [];
        for ($matches = 0; $matches < $count; ) {
            if (!$this->bookingDataIterator->valid()) {
                break;
            }

            $rawBooking = $this->bookingDataIterator->current();
            $this->bookingDataIterator->next();
            $booking = $this->getBooking($rawBooking);

            // Check if the current $booking matches the provided filters.
            if (!$this->applyFilters($booking, $filters)) {
                continue;
            }

            if (($matches-$from) % $count === 0) {
                $this->lastPageItems = new SplQueue();
            }
            $this->lastPageItems->enqueue($booking);

            $data[$lineNumber] = $booking;
            $lineNumber++;
            $matches++;
        }

        // Check next item to know if the end has been reached.
        $this->nextItem = null;
        do {
            // Next item is invalid, so end has been reached.
            if (!$this->bookingDataIterator->valid()) {
                $this->hasEndBeenReached = true;
                break;
            }

            $rawBooking = $this->bookingDataIterator->current();
            $booking = $this->getBooking($rawBooking);

            // If filters apply, store the item. Else continue with the next.
            if ($this->applyFilters($booking, $filters)) {
                $this->nextItem = ['line' => $matches, 'booking' => $booking];
                break;
            }
            $this->bookingDataIterator->next();

        } while (true);

        return $data;
    }

//    /**
//     * Gets the total item count.
//     * @return int
//     */
//    public function getItemCount()
//    {
//        $itemCount = 0;
//        for ($this->bookingDataIterator->rewind();$this->bookingDataIterator->valid();$this->bookingDataIterator->next()) {
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

        foreach ($filters->getFilters() as $filter) {
            $filterField = $filter->getValue();
            $filterName = $filter->getName();
            if (!$filterField) {
                continue;
            }

            $field = $booking->getFieldByName($filterName);
            $bookingValue = $field->getValue();
            $filterFieldValue = $filterField->getValue();
            if (in_array($filterName, $this->atLeastFilterFields)) {
                if ($bookingValue < $filterFieldValue) {
                    return false;
                }
                continue;
            }
            if (is_array($filterFieldValue)) {
                if (!in_array($bookingValue, $filterFieldValue)) {
                    return false;
                }
                continue;
            }
            if ($bookingValue != $filterFieldValue) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks if end has been reached.
     * @return bool TRUE = End has been reached. FALSE = End has not been reached.
     */
    public function hasEndBeenReached()
    {
        return $this->hasEndBeenReached;
    }

    private function rewindAndSkipToFrom($from, $count, $filters = null)
    {
        $this->bookingDataIterator->rewind();

        // Store the last bookings in case $from is higher than the total count of bookings
        $bookingsQueue = new SplQueue();
        if ($this->nextItem != null) {
            $bookingsQueue->enqueue($this->nextItem);
            $from += 1;
        }
        $i = 0;
        while ($i < $from) {
            // If end of bookings have been reached, exit for loop.
            if (!$this->bookingDataIterator->valid()) {
                break;
            }

            $rawBooking = $this->bookingDataIterator->current();
            $booking = $this->getBooking($rawBooking);

            // Check if the current $booking matches the provided filters.
            if (!$this->applyFilters($booking, $filters)) {
                $this->bookingDataIterator->next();
                continue;
            }

            if ($bookingsQueue->count() % $count === 0) {
                $bookingsQueue = new SplQueue();
            }

            // Check if queue limit has been reached.
            if ($bookingsQueue->count() == $count) {
                $bookingsQueue->dequeue();
            }

            $bookingsQueue->enqueue(['line' => $i, 'booking' => $booking]);
            $this->bookingDataIterator->next();
            $i++;
        }
        $this->lastPageItems = $bookingsQueue;
        return $i;
    }

    public function getLastPageItems()
    {
        $lastPage = [];
        while (!$this->lastPageItems->isEmpty() && $data = $this->lastPageItems->dequeue())
        {
            $lastPage[$data['line']] = $data['booking'];
        }
        return $lastPage;
    }
}