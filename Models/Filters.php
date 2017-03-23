<?php

class Filters
{
    private $dataTypeCluster;
    private $action;

    /**
     * FilterConfig constructor.
     * @param $action string Action filter.
     * @param DataTypeCluster $dataTypeCluster Data type cluster which contains the filter config.
     */
    public function __construct($action, DataTypeCluster $dataTypeCluster)
    {
        $this->dataTypeCluster = $dataTypeCluster;
        $this->action = $action;
    }

    public function getAction() : string
    {
        return $this->action;
    }

    public function getIntegerFields() : array
    {
        return $this->dataTypeCluster->getIntegerFields();
    }

    public function getBooleanFields() : array
    {
        return $this->dataTypeCluster->getBooleanFields();
    }

    public function getFloatFields() : array
    {
        return $this->dataTypeCluster->getFloatFields();
    }

    public function getStringFields() : array
    {
        return $this->dataTypeCluster->getStringFields();
    }

    public function getDistanceFields() : array
    {
        return $this->dataTypeCluster->getDistanceFields();
    }

    public function getPriceFields() : array
    {
        return $this->dataTypeCluster->getPriceFields();
    }
}