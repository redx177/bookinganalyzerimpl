<?php

class BookingBuilder
{
    private $dataTypeClusterer;

    public function __construct(ConfigProvider $config, DataTypeClusterer $dataTypeClusterer)
    {
        $this->idField = $config->get('idField');
        $this->dataTypeClusterer = $dataTypeClusterer;
    }

    public function fromRawData($rawData) : Booking
    {
        $dataTypeCluster = $this->dataTypeClusterer->get($rawData);
        $id = $rawData[$this->idField];
        $booking = new Booking($id, $dataTypeCluster);
        return $booking;
    }
}