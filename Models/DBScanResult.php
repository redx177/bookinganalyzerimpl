<?php

/**
 * Created by PhpStorm.
 * User: slang
 * Date: 25.04.17
 * Time: 22:04
 */
class DBScanResult
{
    /**
     * @var array
     */
    private $clusters;
    /**
     * @var Booking[]
     */
    private $noise;

    /**
     * DBScanResult constructor.
     * @param array $clusters
     * @param Booking[] $noise
     */
    public function __construct(array $clusters, array $noise)
    {
        $this->clusters = $clusters;
        $this->noise = $noise;
    }

    /**
     * @return array
     */
    public function getClusters(): array
    {
        return $this->clusters;
    }

    /**
     * @return Booking[]
     */
    public function getNoise(): array
    {
        return $this->noise;
    }
}