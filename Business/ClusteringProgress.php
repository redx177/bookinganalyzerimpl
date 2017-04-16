<?php

/**
 * Saves the clustering progress to a file.
 * Not every call to storeState is saved to file.
 * How often this is done can be configured in the $config['kprototype/dbscan']['outputInterval'].
 */
abstract class ClusteringProgress
{
    private $lastOutput;
    private $fieldNameMapping;
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
        $this->lastOutput = 0;
        $this->rootDir = $config->get('rootDir');

        $clusteringConfig = $this->getClusteringConfig($config);
        $this->outputInterval = $clusteringConfig['outputInterval'];
        $this->outputFile = $clusteringConfig['serviceOutput'];
    }

    /**
     * Stores the state to a file every $config['kprototype/dbscan']['outputInterval'] seconds or if $force=true.
     * @param float $algorithmStartTime Start time of the algorithm.
     * @param int $bookingsCount Count of bookings.
     * @param Clusters $clusters Clustering state.
     * @param int $iteration Current iteration number.
     * @param bool $done TRUE = Algorithm is done. Output is forced to file.
     *                   FALSE = Algorithm is not yet don. Output is stored to file if $config['kprototype/dbscan']['outputInterval'] seconds have passed by.
     */
    public function storeState(float $algorithmStartTime, int $bookingsCount, Clusters $clusters, int $iteration, $done = false)
    {
        $currentTime = microtime(TRUE);
        if ($currentTime - $this->lastOutput > $this->outputInterval || $done) {
            $this->lastOutput = microtime(TRUE);
            $runtime = $currentTime - $algorithmStartTime;

            $count0 = count($clusters->getClusters()[0]->getAssociates());
            $count1 = count($clusters->getClusters()[1]->getAssociates());
            echo "before render\n";
            echo "ID1: {$clusters->getClusters()[0]->getCenter()->getId()}\n";
            echo "ID2: {$clusters->getClusters()[1]->getCenter()->getId()}\n";
            echo "Associates1: {$count0}\n";
            echo "Associates2: {$count1}\n";
            echo "TotalCosts: {$clusters->getTotalCosts()}\n----------------------------------\n";
            $content = $this->template->render([
                'clusters' => $clusters,
                'bookingsCount' => $bookingsCount,
                'runtimeInSeconds' => $runtime,
                'iteration' => $iteration,
                'done' => $done,
            ]);
            file_put_contents($this->rootDir . $this->outputFile, $content);
        }
    }
}