<?php

class Clusters
{
    /**
     * @var Cluster[]
     */
    private $clusters;
    private $centerIds;

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
}