<?php

class FiltersProvider
{
    private $dataTypeClusterer;
    private $destinationFile;

    /**
     * FilterConfigs constructor.
     * @param DataTypeClusterer $dataTypeClusterer Data type clusterer to group raw booking data.
     * @param string $destinationFile File containing all destination as a CSV (; as separator)
     */
    public function __construct(DataTypeClusterer $dataTypeClusterer, $destinationFile)
    {
        $this->dataTypeClusterer = $dataTypeClusterer;
        $this->destinationFile = $destinationFile;
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
     * Gets all destinations (array of countries, regions and places).
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
}