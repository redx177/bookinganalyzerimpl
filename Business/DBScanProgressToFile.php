<?php

/**
 * Saves the DBSCAN progress to a file.
 * Not every call to storeState is saved to file.
 * How often this is done can be configured in the $config['dbscan']['outputInterval'].
 */
class DBScanProgressToFile extends AprioriProgressToFile
{
    public function __construct(ConfigProvider $config, Twig_Environment $twig, Runtime $runtime)
    {
        parent::__construct($config, $twig, $runtime);

        $this->clusterTemplate = $twig->load('dbscanClusters.twig');

        $dbScanConfig = $config->get('dbscan');
        $this->clusteringOutputInterval = $dbScanConfig['outputInterval'];
        $this->clusteringOutputFile = $dbScanConfig['serviceOutput'];
    }
}