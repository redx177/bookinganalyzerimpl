<?php

class DBScanResult implements ClusteringResult
{
    /**
     * @var DBScanCluster[]
     */
    private $clusters = [];
    /**
     * @var ClusterPoint[]
     */
    private $noise = [];

    /**
     * @return DBScanCluster[]
     */
    public function getClusters(): array
    {
        return $this->clusters;
    }

    /**
     * @return ClusterPoint[]
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

    /**
     * Adds a cluster to the result.
     * @param DBScanCluster $cluster
     */
    public function addCluster(DBScanCluster $cluster)
    {
        $this->clusters[] = $cluster;
    }

    /**
     * Adds a noise point to the cluster.
     * @param ClusterPoint $noise
     */
    public function addNoisePoint(ClusterPoint $noise)
    {
        $this->noise[] = $noise;
    }
}