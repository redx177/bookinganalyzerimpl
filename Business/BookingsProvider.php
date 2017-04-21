<?php
class BookingsProvider {
    private $bookingDataIterator;
    private $dataTypeClusterer;
    private $idField;
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
    }

    /**
     * Gets a subset of data.
     * @param int $count Number of elements to get.
     * @param int $from Start index to retrieve data from. Count is starting from index 0
     * @return Booking[] Requested data. Array of Booking
     */
    public function getSubset(int $count, int $from = null)
    {
        $lineNumber = 0;
        if ($from != null && $this->bookingDataIterator->key() != $from) {
            $lineNumber = $this->rewindAndSkipToFrom($from, $count);
        }

        $data = [];
        for ($matches = 0; $matches < $count; ) {
            if (!$this->bookingDataIterator->valid()) {
                break;
            }

            $rawBooking = $this->bookingDataIterator->current();
            $this->bookingDataIterator->next();
            $booking = $this->getBooking($rawBooking);

            if (($matches-$from) % $count === 0) {
                $this->lastPageItems = new SplQueue();
            }
            $this->lastPageItems->enqueue($booking);

            $data[$lineNumber] = $booking;
            $lineNumber++;
            $matches++;
        }

        // Check next item to know if the end has been reached.
        if (!$this->bookingDataIterator->valid()) {
            $this->hasEndBeenReached = true;
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
//        for ($this->bookingDataIterator->rewind();$this->bookingDataIterator->valid();$this->bookingDataIterator->next()) {
//            $itemCount++;
//        }
//        return $itemCount;
//    }

    public function getBooking(array $rawBooking): Booking
    {
        $dataTypeCluster = $this->dataTypeClusterer->get($rawBooking);
        $id = $rawBooking[$this->idField];
        $booking = new Booking($id, $dataTypeCluster);
        return $booking;
    }

    /**
     * Checks if end has been reached.
     * @return bool TRUE = End has been reached. FALSE = End has not been reached.
     */
    public function hasEndBeenReached()
    {
        return $this->hasEndBeenReached;
    }

    private function rewindAndSkipToFrom($from, $count)
    {
        $this->rewind();

        // Store the last bookings in case $from is higher than the total count of bookings
        $bookingsQueue = new SplQueue();
        $i = 0;
        while ($i < $from) {
            // If end of bookings have been reached, exit for loop.
            if (!$this->bookingDataIterator->valid()) {
                break;
            }

            $rawBooking = $this->bookingDataIterator->current();
            $booking = $this->getBooking($rawBooking);

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

    public function rewind()
    {
        $this->bookingDataIterator->rewind();
        $this->hasEndBeenReached = false;
    }

    public function getBookingsCount()
    {
        return $this->bookingDataIterator->count();
    }
}