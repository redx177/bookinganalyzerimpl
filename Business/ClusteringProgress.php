<?php

/**
 * Saves the clustering progress to a file.
 * Not every call to storeState is saved to file.
 * How often this is done can be configured in the $config['kprototype/dbscan']['outputInterval'].
 */
abstract class ClusteringProgress
{
    private $lastOutput;
    private $rootDir;
    private $outputInterval;
    private $outputFile;

    /**
     * @var Twig_TemplateWrapper
     */
    private $template;

    protected abstract function getClusteringConfig(ConfigProvider $config): array;

    public function __construct(ConfigProvider $config, Twig_TemplateWrapper $template)
    {
        $this->template = $template;
        $this->lastOutput = microtime(TRUE);
        $this->rootDir = $config->get('rootDir');

        $clusteringConfig = $this->getClusteringConfig($config);
        $this->outputInterval = $clusteringConfig['outputInterval'];
        $this->outputFile = $clusteringConfig['serviceOutput'];
        $this->maxIterations = $clusteringConfig['maxIterations'];
    }

    /**
     * Stores the state to a file every $config['kprototype/dbscan']['outputInterval'] seconds or if $force=true.
     * @param float $algorithmStartTime Start time of the algorithm.
     * @param int $bookingsCount Count of bookings.
     * @param Clusters $clusters Clustering state.
     * @param int $iteration Current iteration number.
     * @param int $status 0 = Data caching done. 1 = Clustering done. 2 = Apriori done. ($status=2 will force an output, ignoring outputInterval from config)
     */
    public function storeState(float $algorithmStartTime, int $bookingsCount, Clusters $clusters, int $iteration, int $status)
    {
        $currentTime = microtime(TRUE);
        if ($currentTime - $this->lastOutput > $this->outputInterval || $status == 2) {
            $this->lastOutput = microtime(TRUE);
            $runtime = $currentTime - $algorithmStartTime;

//            $count0 = count($clusters->getClusters()[0]->getAssociates());
//            $count1 = count($clusters->getClusters()[1]->getAssociates());
//            echo "before render\n";
//            echo "ID1: {$clusters->getClusters()[0]->getCenter()->getId()}\n";
//            echo "ID2: {$clusters->getClusters()[1]->getCenter()->getId()}\n";
//            echo "Associates1: {$count0}\n";
//            echo "Associates2: {$count1}\n";
//            echo "TotalCosts: {$clusters->getTotalCosts()}\n----------------------------------\n";
            $content = $this->template->render([
                'clusters' => $clusters,
                'bookingsCount' => $bookingsCount,
                'runtimeInSeconds' => $runtime,
                'iteration' => $iteration,
                'status' => $status,
                'pullInterval' => $this->outputInterval,
            ]);
            file_put_contents($this->rootDir . $this->outputFile, $content);
        }
    }
}