<?php

/**
 * Iterator for CSV file.
 * First line will be treatened as header line with names and will never be returned.
 */
class LoadAllCsvDataIterator implements BookingDataIterator {
    private $filePointer;
    private $currentRowNumber = 0;
    private $deliminiter;
    private $enclosure;
    private $data = [];

    /**
     * CsvIterator constructor.
     * @param $file File (incl. path) to load.
     * @param string $deliminiter The optional delimiter parameter sets the field delimiter (one character only).
     * @param string $enclosure The optional enclosure parameter sets the field enclosure character (one character only).
     * @throws Exception
     */
    public function __construct($file, $deliminiter=';', $enclosure = '"')
    {
        if (!file_exists($file)) {
            throw new Exception('File ['. $file. '] not found');
        }
        $this->filePointer = fopen($file, 'r');
        $this->currentRowNumber = 0;
        $this->deliminiter = $deliminiter;
        $this->enclosure = $enclosure;

        $this->initData($file);
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        if (!$this->valid()) {
            return false;
        }
        return $this->data[$this->currentRowNumber];
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
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->currentRowNumber+1;
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
        return array_key_exists($this->currentRowNumber, $this->data) && is_array($this->data);
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
    }

    /**
     * Skips a given amount of lines.
     * @param $count Number of line to skip.
     */
    public function skip($count)
    {
        $this->currentRowNumber += $count;
    }

    private function initData($file)
    {
        $data = file($file);
        $fieldNames = str_getcsv($data[0], $this->deliminiter, $this->enclosure);
        for ($i = 1; $i < count($data); $i++) {
            $line = str_getcsv($data[$i], $this->deliminiter, $this->enclosure);
            $this->data[] = array_combine($fieldNames, $line);
        }
    }
}