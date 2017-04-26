<?php

interface AprioriProgress
{
    /**
     * Sets a state of the apriori algorithm.
     * @param float $algorithmStartTime Start time as a float (microtime()) of the algorithm.
     * @param int $bookingsCount Number of processed bookings.
     * @param array $candidates Candidates of the current item set.
     * @param array $frequentSets Analyzed frequentSets of previous item sets.
     */
    public function storeState(float $algorithmStartTime, int $bookingsCount, array $candidates = null, array $frequentSets = null);

    /**
     * Sets a state of the apriori algorithm.
     * @param KPrototypeResult $clusters All clusters, without apriori data.
     * @param int $status 0 = Data caching done. 1 = Clustering done. 2 = Apriori done. ($status=2 will force an output, ignoring outputInterval from config)
     * @param KPrototypeCluster $cluster Current cluster to render.
     */
    public function storeClusterState(ClusteringResult $clusters, $status, Cluster $cluster = null);

    /**
     * Gets the apriori state.
     * @return AprioriState
     */
    public function getState(): AprioriState;
}