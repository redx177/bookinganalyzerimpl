<?php

class Clusters
{
    private $centerIds;
    private $iteration;

    /**
     * @var Cluster[]
     */
    private $clusters;

    public function __construct($iteration)
    {
        $this->iteration = $iteration;
    }

    public function getIteration(): int
    {
        return $this->iteration;
    }

    public function addCluster(Cluster $cluster)
    {
        $this->clusters[] = $cluster;
        $this->centerIds[] = $cluster->getCenter()->getId();
    }

    /**
     * @return Cluster[]
     */
    public function getClusters()
    {
        return $this->clusters;
    }

    /**
     * Get total costs of all clusters.
     * @return int Cluster costs.
     */
    public function getTotalCosts()
    {
        $costs = 0;
        foreach ($this->getClusters() as $cluster) {
            $costs += $cluster->getTotalCosts();
        }
        return $costs;
    }

    /**
     * Gets the Ids of all centers of the clusters.
     * @return int[] Ids of the centers.
     */
    public function getClusterCenterIds()
    {
        return $this->centerIds;
    }

    public function getBookingsCount()
    {
        $bookingsCount = 0;
        foreach ($this->getClusters() as $cluster) {
            $bookingsCount += count($cluster->getAssociates());
        }
        return $bookingsCount;
    }
}