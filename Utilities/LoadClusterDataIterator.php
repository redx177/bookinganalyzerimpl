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
     * @var Associate[]
     */
    private $associates;

    public function __construct(Cluster $cluster, Redis $redis)
    {
        $this->redis = $redis;
        $this->associates = array_values($cluster->getAssociates());
        $this->rewind();
    }

    public function current()
    {
        return $this->currentLine;
    }

    public function next()
    {
        $this->currentRowNumber++;

        if (!array_key_exists($this->currentRowNumber, $this->associates)) {
            $this->currentLine = false;
            return;
        }
        $id = $this->associates[$this->currentRowNumber]->getId();

        $this->currentLine = $this->redis->hGetAll($id);
        if ($this->currentLine === null) {
            $this->currentLine = false;
        }
    }

    public function key()
    {
        return $this->currentRowNumber;
    }

    public function valid()
    {
        return is_array($this->currentLine) && count($this->currentLine);
    }

    public function rewind()
    {
        $this->currentRowNumber = 0;
        $this->next();
    }

    public function skip($count)
    {
        // "-1" because next() will increment 1.
        $this->currentRowNumber += $count - 1;
        $this->next();
    }

    public function count(): int
    {
        return count($this->associates);
    }
}