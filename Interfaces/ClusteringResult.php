<?php

Interface ClusteringResult
{
    /**
     * Gets the clusters.
     * @return Cluster[]
     */
    public function getClusters();

    /**
     * Gets the count of points in all clusters.
     * @return int
     */
    public function getPointCount(): int;
}