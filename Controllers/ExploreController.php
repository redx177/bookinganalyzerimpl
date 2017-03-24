<?php
/**
 * Created by PhpStorm.
 * User: slang
 * Date: 21.03.17
 * Time: 13:46
 */
 class ExploreController implements Controller {
     private $twig;
     private $bookingsProvider;
     private $pageSize;
     /**
      * @var Pagination
      */
     private $pagination;
     /**
      * @var ConfigProvider
      */
     private $config;
     /**
      * @var FiltersProvider
      */
     private $filtersProvider;

     /**
      * ExploreController constructor.
      * @param Twig_Environment $twig Twig environment for loading templates.
      * @param Pagination $pagination Pagination provider.
      * @param BookingsProvider $bookingsProvider Provider for the data to display.
      * @param ConfigProvider $config Configuration provider.
      * @param FiltersProvider $filtersProvider Filters provider to filter data to explore.
      * @internal param ConfigProvider $config Configuration provider.
      */
     public function __construct(
         Twig_Environment $twig,
         Pagination $pagination,
         BookingsProvider $bookingsProvider,
         ConfigProvider $config,
         FiltersProvider $filtersProvider)
     {
         $this->twig = $twig;
         $this->bookingsProvider = $bookingsProvider;
         $this->pagination = $pagination;
         $this->config = $config;
         $this->filtersProvider = $filtersProvider;
     }


     /**
      * Returns the code to render.
      * @return string Code to render.
      */
     public function render()
     {
         $filters = $this->filtersProvider->get($_GET);
         $data = $this->bookingsProvider->getSubset($this->pagination->getCurrentPageFirstItemIndex(), $this->pagination->getPageSize(), $filters);

         $template = $this->twig->load('explore.twig');
         $indices = array_keys($data);
         $currentFirstIndexOnPage = reset($indices);
         return $template->render(array(
             'bookings' => $data,
             'view' => 'explore',
             'currentPage' => $this->pagination->fixPageValue($currentFirstIndexOnPage),
             'lastPageReached' => $this->pagination->lastPageReached($currentFirstIndexOnPage, count($data)),
             'paginationWindow' => $this->config->get('paginationWindow'),
             'fieldTitles' => $this->config->get('fieldNameMapping'),
             'buttonConfigs' => [new ButtonConfig($this->config->get('filterButtonTitle'), 'apply')],
             '_GET' => $_GET,
            ));
     }
 }