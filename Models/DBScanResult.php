<?php

class DBScanResult implements ClusteringResult
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

    /**
     * Gets the count of points in all clusters.
     * @return int
     */
    public function getPointCount(): int
    {
        $bookingsCount = 0;
        foreach ($this->getClusters() as $cluster) {
            $bookingsCount += count($cluster->getClusterPoints());
        }
        return $bookingsCount;
    }
}