<?php

class DataCache
{
    private $fileCacheDirectory;
    private $atLeastFilterFields;
    private $delimiter;
    private $enclosure;
    private $idField;

    /**
     * @var DataIterator
     */
    private $iterator;
    /**
     * @var DataTypeClusterer
     */
    private $dataTypeClusterer;

    public function __construct(ConfigProvider $config, DataIterator $iterator, DataTypeClusterer $clusterer, $delimiter=';', $enclosure = '"')
    {
        $rootDir = $config->get('rootDir');
        $this->fileCacheDirectory = $rootDir . $config->get('fileCacheDirectory');
        $this->atLeastFilterFields = $config->get('atLeastFilterFields');
        $this->idField = $config->get('idField');

        $this->iterator = $iterator;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->dataTypeClusterer = $clusterer;
    }

    public function getCacheFile(Filters $filters = null) {
        $hash = $this->hash($filters);
        $this->createCacheFolder();
        $cacheFile = $this->fileCacheDirectory . $hash;
        if (!file_exists($cacheFile)) {
            $this->cacheFile($cacheFile, $filters);
        }
        return $cacheFile;
    }

    public function getCountFile(Filters $filters = null) {
        return $this->fileCacheDirectory . $this->hash($filters) . 'count';
    }

    private function cacheFile($cacheFile, $filters = null)
    {
        $fp = fopen($cacheFile, 'w');
        $rawBooking = $this->iterator->current();
        $bookingsCount = 0;
        fputcsv($fp, array_keys($rawBooking), $this->delimiter, $this->enclosure);
        foreach ($this->iterator as $rawBooking) {
            $dataTypeCluster = $this->dataTypeClusterer->get($rawBooking);
            $id = $rawBooking[$this->idField];
            $booking = new Booking($id, $dataTypeCluster);
            if ($this->applyFilters($booking, $filters)) {
                $bookingsCount++;
                fputcsv($fp, $rawBooking, $this->delimiter, $this->enclosure);
            }
        }
        fclose($fp);
        file_put_contents($this->getCountFile($filters), $bookingsCount);
    }

    private function applyFilters(Booking $booking, Filters $filters = null)
    {
        if ($filters === null) {
            return true;
        }

        foreach ($filters->getFilters() as $filter) {
            if (!$filter->hasValue()) {
                continue;
            }
            $filterFieldValue = $filter->getValue();
            $filterName = $filter->getName();

            $field = $booking->getFieldByName($filterName);
            $bookingValue = $field->getValue();
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

    private function createCacheFolder()
    {
        if (is_dir($this->fileCacheDirectory)) {
            return;
        }
        mkdir($this->fileCacheDirectory, 0700, true);
    }

    /**
     * @param Filters $filters
     * @return string
     */
    protected function hash(Filters $filters): string
    {
        return hash('md5', serialize($filters->getFilters()));
    }
}