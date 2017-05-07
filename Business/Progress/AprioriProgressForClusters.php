<?php

/**
 * Saves the apriori progress to a file.
 * Not every call to storeState is saved to file.
 * How often this is done can be configured in the $config['apriori']['outputInterval'].
 */
class AprioriProgressForClusters implements AprioriProgress
{
    private $lastOutput;
    private $fieldNameMapping;
    private $rootDir;
    private $outputInterval;
    private $outputFile;

    public function __construct(ConfigProvider $config)
    {
        $this->lastOutput = 0;
        $this->fieldNameMapping = $config->get('fieldNameMapping');
        $this->rootDir = $config->get('rootDir');

        $aprioriConfig = $config->get('apriori');
        $this->outputInterval = $aprioriConfig['outputInterval'];
        $this->outputFile = $aprioriConfig['serviceOutput'];
    }

    public function storeState(float $algorithmStartTime, int $bookingsCount, array $candidates = null, array $frequentSets = null)
    {
    }

    public function getState(): AprioriState
    {
        return new AprioriState([], null, 0, [], 0);
    }

    public function storeClusterState(ClusteringResult $clusters, $status, Cluster $cluster = null)
    {
    }
}