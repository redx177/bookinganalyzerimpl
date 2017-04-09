<?php

/**
 * Saves the apriori progress to a file.
 * Not every call to processState is saved to the file.
 * How often this is done can be configured in the $config['apriori']['outputInterval'].
 */
class AprioriProgressToMemory implements AprioriProgress
{
    /**
     * @var Twig_TemplateWrapper
     */
    private $template;
    /**
     * @var AprioriState
     */
    private $state;
    private $lastOutput;
    private $fieldNameMapping;
    private $rootDir;
    private $outputInterval;
    private $outputFile;

    public function __construct(ConfigProvider $config, Twig_TemplateWrapper $template)
    {
        $this->template = $template;
        $this->lastOutput = 0;
        $this->fieldNameMapping = $config->get('fieldNameMapping');
        $this->rootDir = $config->get('rootDir');

        $aprioriConfig = $config->get('apriori');
        $this->outputInterval = $aprioriConfig['outputInterval'];
        $this->outputFile = $aprioriConfig['serviceOutput'];
    }

    public function storeState(float $algorithmStartTime, int $bookingsCount, array $candidates = null, array $frequentSets = null)
    {
        $currentTime = microtime(TRUE);
        if ($currentTime - $this->lastOutput > $this->outputInterval || $candidates == null) {
            $this->lastOutput = microtime(TRUE);
            $runtime = $currentTime - $algorithmStartTime;

            $sortedSlicedCandidates = null;
            if ($candidates) {
                usort($candidates, array('AprioriAlgorithm', 'frequentSetSort'));
                // Take the top X candidates. Else there can be thousands of them.
                $sortedSlicedCandidates = array_slice($candidates, 0, 10);
            }

            $this->state = new AprioriState($frequentSets, $sortedSlicedCandidates, $bookingsCount, $this->fieldNameMapping, $runtime);
        }
    }

    public function getState(): AprioriState
    {
        return $this->state;
    }
}