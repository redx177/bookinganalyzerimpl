<?php

class AttributanalysisController implements Controller {
    private $twig;
    private $filtersProvider;
    private $apriori;
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
         AprioriAlgorithm $apriori,
         ConfigProvider $config)
     {
         $this->twig = $twig;
         $this->filtersProvider = $filtersProvider;
         $this->apriori = $apriori;
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
             $filters = $this->filtersProvider->get($_REQUEST);
             $startTime = microtime(TRUE);
             $histograms = $this->apriori->run($filters);
             $endTime = microtime(TRUE);
             $runtime = $endTime - $startTime;
         }

         $template = $this->twig->load('attributanalysis.twig');
         return $template->render(array(
             'view' => 'attributanalysis',
             'histograms' => $histograms,
             'fieldTitles' => $this->config->get('fieldNameMapping'),
             'buttonConfigs' => [new ButtonConfig($this->config->get('runButtonTitle'), 'run')],
             'runtimeInSeconds' => number_format($runtime, 4),
            ));
     }
 }