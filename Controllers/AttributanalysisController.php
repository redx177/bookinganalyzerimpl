<?php

class AttributanalysisController implements Controller {
     private $twig;
     private $bookingsProvider;
     private $pagination;
     private $config;
     private $filtersProvider;
     private $urlGenerator;

     /**
      * ExploreController constructor.
      * @param Twig_Environment $twig Twig environment for loading templates.
      * @param Pagination $pagination Pagination provider.
      * @param BookingsProvider $bookingsProvider Provider for the data to display.
      * @param ConfigProvider $config Configuration provider.
      * @param FiltersProvider $filtersProvider Filters provider to filter data to explore.
      * @param UrlGenerator $urlGenerator Url generator to get parameters to pass to the template.
      * @internal param ConfigProvider $config Configuration provider.
      */
     public function __construct(
         Twig_Environment $twig,
         Pagination $pagination,
         BookingsProvider $bookingsProvider,
         ConfigProvider $config,
         FiltersProvider $filtersProvider,
         UrlGenerator $urlGenerator)
     {
         $this->twig = $twig;
         $this->bookingsProvider = $bookingsProvider;
         $this->pagination = $pagination;
         $this->config = $config;
         $this->filtersProvider = $filtersProvider;
         $this->urlGenerator = $urlGenerator;
     }


     /**
      * Returns the code to render.
      * @return string Code to render.
      */
     public function render()
     {
         if (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'run') {

         }

         $filters = $this->filtersProvider->get($_REQUEST);
         $data = $this->bookingsProvider->getSubset($this->pagination->getCurrentPageFirstItemIndex(), $this->pagination->getPageSize(), $filters);

         $template = $this->twig->load('attributanalysis.twig');
         return $template->render(array(
             'bookings' => $data,
             'view' => 'attributanalysis',
             'fieldTitles' => $this->config->get('fieldNameMapping'),
             'buttonConfigs' => [new ButtonConfig($this->config->get('runButtonTitle'), 'run')],
            ));
     }
 }