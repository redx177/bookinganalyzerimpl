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
        foreach ($dataTypeCluster->getIntegerFields() as $value) {
            array_push($this->filters, new Filter($value, int::class));
        }
        foreach ($dataTypeCluster->getBooleanFields() as  $value) {
            array_push($this->filters, new Filter($value, bool::class));
        }
        foreach ($dataTypeCluster->getFloatFields() as  $value) {
            array_push($this->filters, new Filter($value, float::class));
        }
        foreach ($dataTypeCluster->getStringFields() as  $value) {
            array_push($this->filters, new Filter($value, string::class));
        }
        foreach ($dataTypeCluster->getPriceFields() as  $value) {
            array_push($this->filters, new Filter($value, Price::class));
        }
        foreach ($dataTypeCluster->getDistanceFields() as $value) {
            array_push($this->filters, new Filter($value, Distance::class));
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