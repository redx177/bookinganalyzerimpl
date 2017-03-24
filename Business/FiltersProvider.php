<?php

class FiltersProvider
{
    private $dataTypeClusterer;

    /**
     * FilterConfigs constructor.
     * @param DataTypeClusterer $dataTypeClusterer Data type clusterer to group raw booking data.
     */
    public function __construct(DataTypeClusterer $dataTypeClusterer)
    {
        $this->dataTypeClusterer = $dataTypeClusterer;
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
}