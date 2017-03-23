<?php
class Booking
{
    private $dataTypeCluster;

    /**
     * Booking constructor.
     * @param DataTypeCluster $dataTypeCluster Data type cluster which contains the booking data.
     */
    public function __construct(DataTypeCluster $dataTypeCluster)
    {
        $this->dataTypeCluster = $dataTypeCluster;
    }

    public function getId() : int
    {
        return $this->dataTypeCluster->getId();
    }

    public function getIntegerFields() : array
    {
        return $this->dataTypeCluster->getIntegerFields();
    }

    public function getBooleanFields() : array
    {
        return $this->dataTypeCluster->getbbooleanFields();
    }

    public function getFloatFields() : array
    {
        return $this->dataTypeCluster->getFloatFields();
    }

    public function getStringFields(): array
    {
        return $this->dataTypeCluster->getStringFields();
    }

    public function getPriceFields(): array
    {
        return $this->dataTypeCluster->getPriceFields();
    }

    public function getDistanceFields(): array
    {
        return $this->dataTypeCluster->getDistanceFields();
    }
}