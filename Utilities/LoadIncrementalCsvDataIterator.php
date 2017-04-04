<?php

/**
 * Iterator for CSV file.
 * First line will be treatened as header line with names and will never be returned.
 */
class LoadIncrementalCsvDataIterator implements BookingDataIterator {
    private $filePointer;
    private $currentRowNumber = 0;
    private $currentLine = false;
    private $deliminiter;
    private $enclosure;
    private $fieldNames;

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

        $this->fieldNames = $this->getFieldNames();

        // Loading first line.
        $this->next();
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
        $currentLine = fgetcsv($this->filePointer, 0, $this->deliminiter, $this->enclosure);


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
        return fgetcsv($this->filePointer, 0, $this->deliminiter, $this->enclosure);
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
}