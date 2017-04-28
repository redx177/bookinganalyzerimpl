<?php

class DBScanCluster implements Cluster
{
    /**
     * @var ClusterPoint[]
     */
    private $clusterPoints;

    /**
     * @return ClusterPoint[]
     */
    public function getClusterPoints(): array
    {
        return $this->clusterPoints;
    }

    /**
     * Sets the histograms for the apriori algorithm.
     * @param Histograms $histograms
     */
    public function setHistograms(Histograms $histograms)
    {
        $this->histograms = $histograms;
    }

    /**
     * Adds a cluster point to the cluster.
     * @param ClusterPoint $clusterPoint
     */
    public function addClusterPoint($clusterPoint)
    {
        $this->clusterPoints[] = $clusterPoint;
    }
}