<?php

class AttributanalysisController implements Controller {
    private $twig;
    private $filtersProvider;
    private $config;

    /**
     * ExploreController constructor.
     * @param Twig_Environment $twig Twig environment for loading templates.
     * @param FiltersProvider $filtersProvider Filters provider to filter data to explore.
     * @param AprioriAlgorithm $apriori Implementation of the apriori algorithm.
     * @param ConfigProvider $config Configuration provider.
     */
     public function __construct(
         Twig_Environment $twig,
         FiltersProvider $filtersProvider,
         ConfigProvider $config)
     {
         $this->twig = $twig;
         $this->filtersProvider = $filtersProvider;
         $this->config = $config;
     }


     /**
      * Returns the code to render.
      * @return string Code to render.
      */
     public function render()
     {
         $histograms = [];
         $runtime = 0;

         if (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'run') {
             $home = dirname(__DIR__) . '/Services/Apriori';
             // php %s/apriori.php -q  : Run apriori.php script in with -q (quiet mode).
             // > %s/wip/output.txt    : STDOUT is saved to 'some/path/output.txt'.
             // 2>&1                   : STDERR is redirected into STDOUT. 'output.txt' aswell.
             // &                      : Run in the background.
             // echo $! > %s/wip/%s    : PID (process id) is saved to a file.
             exec(sprintf('php %s/apriori.php > %s/wip/output.txt 2>&1 & echo $! > %s%s', $home, $home, dirname(__DIR__), $this->config->get('aprioriServicePidFile')));
         }

         $template = $this->twig->load('attributanalysis.twig');
         return $template->render([
             'view' => 'attributanalysis',
             'histograms' => $histograms,
             'fieldTitles' => $this->config->get('fieldNameMapping'),
             'buttonConfigs' => [new ButtonConfig($this->config->get('runButtonTitle'), 'run')],
             'statusUrl' => $this->config->get('aprioriServiceOutput'),
             'pullInterval' => $this->config->get('aprioriOutputInterval'),
            ]);
     }
 }