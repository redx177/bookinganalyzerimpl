<?php
class Booking
{
    private $id;
    private $dataTypeCluster;

    /**
     * Booking constructor.
     * @param $id int ID of the booking
     * @param $dataTypeCluster DataTypeCluster Data type cluster of  the booking data.
     */
    public function __construct(int $id, DataTypeCluster $dataTypeCluster)
    {
        $this->id = $id;
        $this->dataTypeCluster = $dataTypeCluster;
    }

    public function getId() : int
    {
        return $this->id;
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