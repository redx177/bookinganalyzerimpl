<?php

class CsvIterator implements Iterator {
    /**
     * File pointer to the csv file.
     * @var resource
     */
    private $filePointer;

    /**
     * Current row number the iterator points to.
     * @var int
     */
    private $currentRowNumber;

    /**
     * Field deliminiter of the csv file.
     * @var string
     */
    private $deliminiter;


    /**
     * Field enclosure of the csv file.
     * @var string
     */
    private $enclosure;


    /**
     * Contains the current line data.
     * @var
     */
    private $currentLine = false;

    /**
     * CsvIterator constructor.
     * @param $file File (incl. path) to load.
     * @param string $deliminiter The optional delimiter parameter sets the field delimiter (one character only).
     * @param string $enclosure The optional enclosure parameter sets the field enclosure character (one character only).
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
        $this->currentLine = fgetcsv($this->filePointer, 0, $this->deliminiter, $this->enclosure);
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

        return !feof($this->filePointer);
        if (!$this->next()) {
            fclose($this->filePointer);
            return false;
        }
        return true;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->currentLine = false;
        $this->currentRowNumber = 0;
        rewind($this->filePointer);
    }
}