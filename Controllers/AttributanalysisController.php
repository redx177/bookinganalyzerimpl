<?php

class AttributanalysisController implements Controller
{
    private $twig;
    private $filtersProvider;
    private $rootDir;
    private $pidFile;
    private $fieldNameMapping;
    private $serviceOutput;
    private $outputInterval;
    private $runButtonTitle;

    /**
     * ExploreController constructor.
     * @param Twig_Environment $twig Twig environment for loading templates.
     * @param FiltersProvider $filtersProvider Filters provider to filter data to explore.
     * @param ConfigProvider $config Configuration provider.
     */
    public function __construct(
        Twig_Environment $twig,
        FiltersProvider $filtersProvider,
        ConfigProvider $config)
    {
        $this->twig = $twig;
        $this->filtersProvider = $filtersProvider;
        $this->rootDir = $config->get('rootDir');
        $this->fieldNameMapping = $config->get('fieldNameMapping');
        $this->runButtonTitle = $config->get('runButtonTitle');

        $aprioriConfig = $config->get('apriori');
        $this->pidFile = $aprioriConfig['servicePidFile'];
        $this->serviceOutput = $aprioriConfig['serviceOutput'];
        $this->outputInterval = $aprioriConfig['outputInterval'];
    }


    /**
     * Returns the code to render.
     * @return string Code to render.
     */
    public function render()
    {
        $isRunning = false;
        if (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'run') {
            file_put_contents($this->rootDir . $this->serviceOutput, '');
            $args = escapeshellarg(http_build_query($_REQUEST));
            $home = $this->rootDir . '/Services/Apriori';
            // php %s/apriori.php -q  : Run apriori.php script.
            // > %s/wip/output.txt    : STDOUT is saved to 'some/path/wip/output.txt'.
            // 2>&1                   : STDERR is redirected into STDOUT. 'output.txt' aswell.
            // &                      : Run in the background.
            // echo $! > %s/wip/%s    : PID (process id) is saved to a file.
            exec(sprintf('php %s/apriori.php %s > %s/wip/output.txt 2>&1 & echo $! > %s%s', $home, $args, $home, $this->rootDir, $this->pidFile));
            $isRunning = true;
        }

        $template = $this->twig->load('attributanalysis.twig');
        return $template->render([
            'view' => 'attributanalysis',
            'fieldTitles' => $this->fieldNameMapping,
            'buttonConfigs' => [new ButtonConfig($this->runButtonTitle, 'run')],
            'statusUrl' => $this->serviceOutput,
            'pullInterval' => $this->outputInterval,
            'isRunning' => $isRunning,
            '_REQUEST' => $_REQUEST,
            'destinations' => $this->filtersProvider->getDestinations(),
        ]);
    }
}