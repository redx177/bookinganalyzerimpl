<?php

/**
 * Saves the clustering progress to a file.
 * Not every call to storeState is saved to file.
 * How often this is done can be configured in the $config['kprototype/dbscan']['outputInterval'].
 */
abstract class ClusteringProgress
{
    private $rootDir;
    private $outputInterval;
    private $outputFile;

    /**
     * @var Twig_TemplateWrapper
     */
    private $template;
    /**
     * @var Runtime
     */
    private $runtime;

    protected abstract function getClusteringConfig(ConfigProvider $config): array;

    public function __construct(ConfigProvider $config, Twig_TemplateWrapper $template, Runtime $runtime)
    {
        $this->template = $template;
        $this->runtime = $runtime;
        $this->rootDir = $config->get('rootDir');

        $clusteringConfig = $this->getClusteringConfig($config);
        $this->outputInterval = $clusteringConfig['outputInterval'];
        $this->outputFile = $clusteringConfig['serviceOutput'];
        $this->maxIterations = $clusteringConfig['maxIterations'];
    }

    /**
     * Stores the state to a file every $config['kprototype/dbscan']['outputInterval'] seconds or if $force=true.
     * @param int $bookingsCount Count of bookings.
     * @param KPrototypeResult $clusters Clustering state.
     * @param int $status 0 = Data caching done. 1 = Clustering done. 2 = Apriori done. ($status=2 will force an output, ignoring outputInterval from config)
     */
    public function storeState(int $bookingsCount, KPrototypeResult $clusters, int $status)
    {
        if ($this->runtime->fromLastTick() > $this->outputInterval || $status == 2) {
            echo "clustering write output\n";
            $content = $this->template->render([
                'KPrototypeResult' => $clusters,
                'bookingsCount' => $bookingsCount,
                'runtimeInSeconds' => $this->runtime->fromBeginning(),
                'status' => $status,
                'pullInterval' => $this->outputInterval,
            ]);
            file_put_contents($this->rootDir . $this->outputFile, $content);
            $this->runtime->tick();
        }
    }
}