<?php

/**
 * Saves the KPrototype progress to a file.
 * Not every call to storeState is saved to file.
 * How often this is done can be configured in the $config['kprototype']['outputInterval'].
 */
class KPrototypeProgressToFile extends AprioriProgressToFile
{
    public function __construct(ConfigProvider $config, Twig_Environment $twig, Runtime $runtime)
    {
        parent::__construct($config, $twig, $runtime);

        $this->clusterTemplate = $twig->load('kprototypeClusters.twig');

        $dbScanConfig = $config->get('kprototype');
        $this->clusteringOutputInterval = $dbScanConfig['outputInterval'];
        $this->clusteringOutputFile = $dbScanConfig['serviceOutput'];
    }
}