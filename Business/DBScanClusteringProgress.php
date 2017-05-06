<?php

class DBScanClusteringProgress extends ClusteringProgress
{
    /**
     * @var Twig_TemplateWrapper
     */
    private $template;

    public function __construct(ConfigProvider $config, Runtime $runtime, Twig_Environment $twig)
    {
        parent::__construct($config, $runtime);
        $this->template = $twig->load('dbscanClusters.twig');
    }

    function getClusteringConfig(ConfigProvider $config): array {
        return $config->get('dbscan');
    }

    protected function getClusteringTemplate(): Twig_TemplateWrapper
    {
        return $this->template;
    }
}