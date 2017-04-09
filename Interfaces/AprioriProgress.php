<?php

interface AprioriProgress
{
    /**
     * Gets a state of the apriori algorithm for processing.
     * @param float $algorithmStartTime Start time as a float (microtime()) of the algorithm.
     * @param int $bookingsCount Number of processed bookings.
     * @param array $candidates Candidates of the current item set.
     * @param array $frequentSets Analyzed frequentSets of previous item sets.
     */
    public function processState(float $algorithmStartTime, int $bookingsCount, array $candidates = null, array $frequentSets = null);
}