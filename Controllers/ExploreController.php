<?php

 class ExploreController implements Controller {
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
         $filters = $this->filtersProvider->get($_REQUEST);
         $data = $this->bookingsProvider->getSubset($this->pagination->getPageSize(), $filters, $this->pagination->getCurrentPageFirstItemIndex());
         if (!$data) {
             $data = $this->bookingsProvider->getLastPageItems();
         }

         $template = $this->twig->load('explore.twig');
         $indices = array_keys($data);
         $currentFirstIndexOnPage = reset($indices);
         return $template->render(array(
             'bookings' => $data,
             'view' => 'explore',
             'currentPage' => $this->pagination->fixPageValue($currentFirstIndexOnPage),
             'lastPageReached' => $this->bookingsProvider->hasEndBeenReached(),
             'paginationWindow' => $this->config->get('paginationWindow'),
             'fieldTitles' => $this->config->get('fieldNameMapping'),
             'buttonConfigs' => [new ButtonConfig($this->config->get('filterButtonTitle'), 'apply')],
             '_REQUEST' => $_REQUEST,
             'searchUrlParameters' => $this->urlGenerator->getParameters($filters),
            ));
     }
 }