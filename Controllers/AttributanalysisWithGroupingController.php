<?php

class AttributanalysisWithGroupingController implements Controller {
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

         $kprototypeConfig = $config->get('kprototype');
         $this->pidFile = $kprototypeConfig['servicePidFile'];
         $this->serviceOutput = $kprototypeConfig['serviceOutput'];
         $this->outputInterval = $kprototypeConfig['outputInterval'];
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
             $home = $this->rootDir . '/Services/KPrototype';
             // php %s/apriori.php -q  : Run apriori.php script in with -q (quiet mode).
             // > %s/wip/output.txt    : STDOUT is saved to 'some/path/output.txt'.
             // 2>&1                   : STDERR is redirected into STDOUT. 'output.txt' aswell.
             // &                      : Run in the background.
             // echo $! > %s/wip/%s    : PID (process id) is saved to a file.
             exec(sprintf('php %s/kprototype.php %s > %s/wip/output.txt 2>&1 & echo $! > %s%s', $home, $args, $home, $this->rootDir, $this->pidFile));
             $isRunning = true;
         }

         $template = $this->twig->load('attributanalysisWithGrouping.twig');
         return $template->render([
             'view' => 'attributanalysisWithGrouping',
             'fieldTitles' => $this->fieldNameMapping,
             'buttonConfigs' => [new ButtonConfig($this->runButtonTitle, 'run')],
             'statusUrl' => $this->serviceOutput,
             'pullInterval' => $this->outputInterval,
             'isRunning' => $isRunning,
             '_REQUEST' => $_REQUEST,
            ]);
     }
 }