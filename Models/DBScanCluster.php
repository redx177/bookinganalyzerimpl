<?php

class DBScanCluster implements Cluster
{
    /**
     * @var ClusterPoint[]
     */
    private $clusterPoints;

    /**
     * DBScanCluster constructor.
     * @param ClusterPoint[] $clusterPoints
     */
    public function __construct($clusterPoints)
    {
        $this->clusterPoints = $clusterPoints;
    }

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
}