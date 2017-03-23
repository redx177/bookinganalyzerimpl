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

    public function get($rawData)
    {
        $dataTypeCluster = $this->dataTypeClusterer->get($rawData);
        return new Filters($rawData['action'], $dataTypeCluster);
    }
}