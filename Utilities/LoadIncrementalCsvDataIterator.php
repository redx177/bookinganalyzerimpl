<?php

/**
 * Iterator for CSV file.
 * First line will be treatened as header line with names and will never be returned.
 */
class LoadIncrementalCsvDataIterator implements BookingDataIterator {
    private $filePointer;
    private $currentRowNumber = 0;
    private $currentLine = false;
    private $delimiter;
    private $enclosure;
    private $fieldNames;
    private $count;

    /**
     * CsvIterator constructor.
     * @param string $dataFile File (incl. path) to load.
     * @param string|null $countFile File with the count.
     * @param string|null $delimiter The optional delimiter parameter sets the field delimiter (one character only).
     * @param string|null $enclosure The optional enclosure parameter sets the field enclosure character (one character only).
     * @throws Exception
     */
    public function __construct(string $dataFile, string $countFile = null, $delimiter=';', $enclosure = '"')
    {
        if (!file_exists($dataFile)) {
            throw new Exception('File ['. $dataFile. '] not found');
        }
        $this->filePointer = fopen($dataFile, 'r');
        $this->currentRowNumber = 0;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;

        $this->fieldNames = $this->getFieldNames();

        // Loading first line.
        $this->next();

        $this->setCount($countFile);
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
        $currentLine = fgetcsv($this->filePointer, 0, $this->delimiter, $this->enclosure);


        if ($currentLine === false) {
            $this->currentLine = false;
        } else {
            // Combine fieldNames and currentLine to an associative array.
            $this->currentLine = array_combine($this->fieldNames, $currentLine);
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
        return is_array($this->currentLine);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        rewind($this->filePointer);

        // Skipping field name line.
        $this->next();

        $this->currentRowNumber = 0;

        // Loading first line.
        $this->next();
    }

    private function getFieldNames()
    {
        rewind($this->filePointer);
        return fgetcsv($this->filePointer, 0, $this->delimiter, $this->enclosure);
    }

    /**
     * Skips a given amount of lines.
     * @param $count Number of line to skip.
     */
    public function skip($count)
    {
        for($i = 0; $i < $count; $i++) {
            $this->next();
        }
    }

    private function setCount($countFile)
    {
        if ($countFile === null) {
            $this->count = 0;
            return;
        }
        $this->count = (int)file_get_contents($countFile);
    }

    /**
     * Gets the total amount of bookings.
     * @return int Total amount of bookings
     */
    public function count(): int
    {
        return $this->count();
    }
}