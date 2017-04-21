<?php

/**
 * Iterator for redis store.
 */
class LoadClusterDataIterator implements BookingDataIterator
{
    private $currentLine = false;
    private $currentRowNumber;
    /**
     * @var Redis
     */
    private $redis;
    /**
     * @var Cluster
     */
    private $cluster;

    public function __construct(Cluster $cluster)
    {
        $this->cluster = $cluster;
        $this->rewind();
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->currentLine;
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->currentRowNumber++;
        $this->currentLine = $this->redis->hGetAll($this->currentRowNumber);
        if ($this->currentLine === null) {
            $this->currentLine = false;
        }
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->currentRowNumber;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return is_array($this->currentLine) && count($this->currentLine);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->currentRowNumber = 0;
        $this->next();
    }

    /**
     * Skips a given amount of lines.
     * @param $count Number of line to skip.
     */
    public function skip($count)
    {
        // "-1" because next() will increment 1.
        $this->currentRowNumber += $count - 1;
        $this->next();
    }

    /**
     * Gets the total amount of bookings.
     * @return int Total amount of bookings
     */
    public function count(): int
    {
        return $this->redis->get('bookingsCount');
    }
}