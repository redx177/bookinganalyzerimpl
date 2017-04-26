<?php

class AttributanalysisWithGroupingController implements Controller
{
    private $twig;
    private $filtersProvider;
    private $rootDir;
    private $fieldNameMapping;
    private $kprototypeConfig;

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

        $this->kprototypeConfig = $config->get('kprototype');
        $this->dbscanConfig = $config->get('dbscan');
    }


    /**
     * Returns the code to render.
     * @return string Code to render.
     */
    public function render()
    {
        $isKPrototypeRunning = false;
        $serviceOutput = '';
        $outputInterval = 0;
        if (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'runkprototype') {
            $this->renderKPrototype();
            $isKPrototypeRunning = true;
            $serviceOutput = $this->kprototypeConfig['serviceOutput'];
            $outputInterval = $this->kprototypeConfig['outputInterval'];
        }

        $isDbScanRunning = false;
        if (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'rundbscan') {
            $isDbScanRunning = true;
            $serviceOutput = $this->dbscanConfig['serviceOutput'];
            $outputInterval = $this->dbscanConfig['outputInterval'];
        }


        $template = $this->twig->load('attributanalysisWithGrouping.twig');
        return $template->render([
            'view' => 'attributanalysisWithGrouping',
            'fieldTitles' => $this->fieldNameMapping,
            'buttonConfigs' => [
                new ButtonConfig($this->kprototypeConfig['runButtonTitle'], 'runkprototype'),
                new ButtonConfig($this->dbscanConfig['runButtonTitle'], 'rundbscan')
            ],
            'statusUrl' => $serviceOutput,
            'pullInterval' => $outputInterval,
            'isRunning' => $isKPrototypeRunning || $isDbScanRunning,
            '_REQUEST' => $_REQUEST,
        ]);
    }

    protected function renderKPrototype()
    {
        file_put_contents($this->rootDir . $this->kprototypeConfig['serviceOutput'], '');
        $args = escapeshellarg(http_build_query($_REQUEST));
        $home = $this->rootDir . '/Services/KPrototype';
        // php %s/apriori.php -q  : Run apriori.php script in with -q (quiet mode).
        // > %s/wip/output.txt    : STDOUT is saved to 'some/path/output.txt'.
        // 2>&1                   : STDERR is redirected into STDOUT. 'output.txt' aswell.
        // &                      : Run in the background.
        // echo $! > %s/wip/%s    : PID (process id) is saved to a file.
        exec(sprintf('php %s/kprototype.php %s > %s/wip/output.txt 2>&1 & echo $! > %s%s', $home, $args, $home, $this->rootDir, $this->kprototypeConfig['servicePidFile']));
    }

    protected function runDBScan()
    {
        file_put_contents($this->rootDir . $this->kprototypeConfig['serviceOutput'], '');
        $args = escapeshellarg(http_build_query($_REQUEST));
        $home = $this->rootDir . '/Services/DBScan';
        // php %s/apriori.php -q  : Run apriori.php script in with -q (quiet mode).
        // > %s/wip/output.txt    : STDOUT is saved to 'some/path/output.txt'.
        // 2>&1                   : STDERR is redirected into STDOUT. 'output.txt' aswell.
        // &                      : Run in the background.
        // echo $! > %s/wip/%s    : PID (process id) is saved to a file.
        exec(sprintf('php %s/dbscan.php %s > %s/wip/output.txt 2>&1 & echo $! > %s%s', $home, $args, $home, $this->rootDir, $this->kprototypeConfig['servicePidFile']));
    }
}