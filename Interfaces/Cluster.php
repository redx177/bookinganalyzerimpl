<?php

interface Cluster
{
    /**
     * Gets the associates in the cluster.
     * @return ClusterPoint[]
     */
    public function getClusterPoints();

    /**
     * Sets the histograms for the apriori algorithm.
     * @param Histograms $histograms
     */
    public function setHistograms(Histograms $histograms);
}