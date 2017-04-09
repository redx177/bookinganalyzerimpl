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
     * Gets the apriori state.
     * @return AprioriState
     */
    public function getState(): AprioriState;
}