<?php

class Filters
{
    private $action;
    private $filters = [];

    /**
     * FilterConfig constructor.
     * @param $action string Action filter.
     * @param DataTypeCluster $dataTypeCluster Data type cluster which contains the filter config.
     */
    public function __construct(string $action, DataTypeCluster $dataTypeCluster)
    {
        $this->action = $action;
        foreach ($dataTypeCluster->getIntegerFields() as $key => $value) {
            array_push($this->filters, new Filter($key, $value, int::class));
        }
        foreach ($dataTypeCluster->getBooleanFields() as $key => $value) {
            array_push($this->filters, new Filter($key, $value, bool::class));
        }
        foreach ($dataTypeCluster->getFloatFields() as $key => $value) {
            array_push($this->filters, new Filter($key, $value, float::class));
        }
        foreach ($dataTypeCluster->getStringFields() as $key => $value) {
            array_push($this->filters, new Filter($key, $value, string::class));
        }
        foreach ($dataTypeCluster->getPriceFields() as $key => $value) {
            array_push($this->filters, new Filter($key, $value, Price::class));
        }
        foreach ($dataTypeCluster->getDistanceFields() as $key => $value) {
            array_push($this->filters, new Filter($key, $value, Distance::class));
        }
    }

    public function getAction() : string
    {
        return $this->action;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function getFiltersByType(string $type) : array
    {
        $filters = [];
        foreach ($this->filters as $filter) {
            if ($type == $filter->getType()) {
                array_push($filters, $filter);
            }
        }
        return $filters;
    }
}