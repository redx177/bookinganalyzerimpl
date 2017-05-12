<?php

class FiltersProvider
{
    private $dataTypeClusterer;
    private $destinationFile;
    private $customerDestinationFile;

    /**
     * FilterConfigs constructor.
     * @param DataTypeClusterer $dataTypeClusterer Data type clusterer to group raw booking data.
     * @param string $destinationFile File containing all destination as a CSV (; as separator).
     * @param string $customerDestinationFile File containing all customer destinations as a CSV (; as separator).
     */
    public function __construct(DataTypeClusterer $dataTypeClusterer, $destinationFile, $customerDestinationFile)
    {
        $this->dataTypeClusterer = $dataTypeClusterer;
        $this->destinationFile = $destinationFile;
        $this->customerDestinationFile = $customerDestinationFile;
    }

    /**
     * Gets the filters provided by $rawData parameter.
     * @param array $rawData Filter raw data.
     * @return Filters Filter data.
     */
    public function get(array $rawData)
    {
        $dataTypeCluster = $this->dataTypeClusterer->get($rawData);
        $action = array_key_exists('action', $rawData) ? $rawData['action'] : '';
        return new Filters($action, $dataTypeCluster);
    }

    /**
     * Gets all destinations of objects (array of countries, regions and places).
     * @return array Country, regions and places of all accommodations.
     */
    public function getDestinations()
    {
        if (!file_exists($this->destinationFile)) {
            return [];
        }
        if (($handle = fopen($this->destinationFile, 'r')) !== FALSE)
        {
            $destinations = [];
            while (($data = fgetcsv($handle, 1000, ';')) !== FALSE)
            {
                $destinations[] = $data;
            }
            return $destinations;
        }
        return [];
    }

    /**
     * Gets all destinations of customers (array of countries and places).
     * @return array Country and places of all accommodations.
     */
    public function getCustomerDestinations()
    {
        if (!file_exists($this->customerDestinationFile)) {
            return [];
        }
        if (($handle = fopen($this->customerDestinationFile, 'r')) !== FALSE)
        {
            // Discard first row. It contains the field names.
            fgetcsv($handle, 1000, ';', '"');
            $destinations = [];
            while (($data = fgetcsv($handle, 1000, ';', '"')) !== FALSE)
            {
                $destinations[] = $data;
            }
            return $destinations;
        }
        return [];
    }
}