<?php

/**
 * Saves the apriori progress to a file.
 * Not every call to storeState is saved to file.
 * How often this is done can be configured in the $config['apriori']['outputInterval'].
 */
class AprioriProgressToFile implements AprioriProgress
{
    private $lastOutput;
    private $fieldNameMapping;
    private $rootDir;
    private $aprioriOutputInterval;
    private $aprioriOutputFile;
    private $kprototypeOutputInterval;
    private $kprototypeOutputFile;
    private $status;

    /**
     * @var Twig_Environment
     */
    private $twig;
    /**
     * @var array
     */
    private $currentCluster;
    /**
     * @var array
     */
    private $analyzedClusters;
    /**
     * @var Clusters
     */
    private $clusters;
    /**
     * @var Runtime
     */
    private $runtime;

    public function __construct(ConfigProvider $config, Twig_Environment $twig, Runtime $runtime)
    {
        $this->twig = $twig;
        $this->runtime = $runtime;
        $this->lastOutput = 0;
        $this->fieldNameMapping = $config->get('fieldNameMapping');
        $this->rootDir = $config->get('rootDir');

        $aprioriConfig = $config->get('apriori');
        $this->aprioriOutputInterval = $aprioriConfig['outputInterval'];
        $this->aprioriOutputFile = $aprioriConfig['serviceOutput'];

        $kprototypeConfig = $config->get('kprototype');
        $this->kprototypeOutputInterval = $kprototypeConfig['outputInterval'];
        $this->kprototypeOutputFile = $kprototypeConfig['serviceOutput'];
    }

    public function storeState(float $algorithmStartTime, int $bookingsCount, array $candidates = null, array $frequentSets = null)
    {
        if ($this->status != null) {
            $this->storeStateForClusters($algorithmStartTime, $bookingsCount, $candidates, $frequentSets);
            return;
        }

        if ($this->runtime->fromLastTick() > $this->aprioriOutputInterval || $candidates == null) {
            echo "apriori write output\n";

            $sortedSlicedCandidates = null;
            if ($candidates) {
                usort($candidates, array('AprioriAlgorithm', 'frequentSetSort'));
                // Take the top X candidates. Else there can be thousands of them.
                $sortedSlicedCandidates = array_slice($candidates, 0, 10);
            }

            $template = $this->twig->load('apriori.twig');
            $content = $template->render([
                'frequentSets' => $frequentSets,
                'candidates' => $sortedSlicedCandidates,
                'candidatesCount' => count($candidates),
                'bookingsCount' => $bookingsCount,
                'fieldTitles' => $this->fieldNameMapping,
                'runtimeInSeconds' => $this->runtime->fromBeginning(),
                'done' => $candidates === null,
                'pullInterval' => $this->aprioriOutputInterval,
            ]);
            file_put_contents($this->rootDir . $this->aprioriOutputFile, $content);
            $this->runtime->tick();
        }
    }

    public function getState(): AprioriState
    {
        return new AprioriState([], null, 0, [], 0);
    }

    public function storeClusterState(Clusters $clusters, $status, Cluster $cluster = null)
    {
        if ($this->currentCluster != null) {
            $this->analyzedClusters[] = $this->currentCluster;
        }
        if ($cluster == null) {
            $this->currentCluster = null;
        } else {
            $this->currentCluster = ['cluster' => $cluster];
        }
        $this->clusters = $clusters;
        $this->status = $status;
    }

    private function storeStateForClusters($algorithmStartTime, $bookingsCount, $candidates, $frequentSets)
    {
        if ($this->status != 2) {
            $this->currentCluster['candidates'] = $candidates;
            $this->currentCluster['candidatesCount'] = count($candidates);
            $this->currentCluster['frequentSets'] = $frequentSets;
        }

        if ($this->runtime->fromLastTick() > $this->kprototypeOutputInterval || $this->status == 2) {
            echo "clustering apriori write output\n";

            $template = $this->twig->load('clusters.twig');
            $content = $template->render([
                'currentCluster' => $this->currentCluster,
                'analyzedClusters' => $this->analyzedClusters,
                'clusters' => $this->clusters,
                'bookingsCount' => $this->clusters->getBookingsCount(),
                'fieldTitles' => $this->fieldNameMapping,
                'runtimeInSeconds' => $this->runtime->fromBeginning(),
                'status' => 1,
                'pullInterval' => $this->kprototypeOutputInterval,
                'status' => $this->status,
            ]);
            file_put_contents($this->rootDir . $this->kprototypeOutputFile, $content);
            $this->runtime->tick();
        }
    }
}