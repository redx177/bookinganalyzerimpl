<?php

class Filters
{
    private $action;

    /**
     * @var Filter[]
     */
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
            $filter = new Filter($value, int::class);
            if ($filter->hasValue()) {
                array_push($this->filters, $filter);
            }
        }
        foreach ($dataTypeCluster->getBooleanFields() as $value) {
            $filter = new Filter($value, bool::class);
            if ($filter->hasValue()) {
                array_push($this->filters, $filter);
            }
        }
        foreach ($dataTypeCluster->getFloatFields() as $value) {
            $filter = new Filter($value, float::class);
            if ($filter->hasValue()) {
                array_push($this->filters, $filter);
            }
        }
        foreach ($dataTypeCluster->getStringFields() as $value) {
            $filter = new Filter($value, string::class);
            if ($filter->hasValue()) {
                array_push($this->filters, $filter);
            }
        }
        foreach ($dataTypeCluster->getPriceFields() as $value) {
            $filter = new Filter($value, Price::class);
            if ($filter->hasValue()) {
                array_push($this->filters, $filter);
            }
        }
        foreach ($dataTypeCluster->getDistanceFields() as $value) {
            $filter = new Filter($value, Distance::class);
            if ($filter->hasValue()) {
                array_push($this->filters, $filter);
            }
        }
    }

    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Gets the filter fields.
     * @return Filter[] Filter fields.
     */
    public function getFilters()
    {
        return $this->filters;
    }

    public function hasFilter($name)
    {
        foreach ($this->filters as $filter) {
            if ($filter->getName() == $name) {
                return true;
            }
        }
        return false;
    }

    public function getFiltersByType(string $type): array
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