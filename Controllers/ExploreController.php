<?php
/**
 * Created by PhpStorm.
 * User: slang
 * Date: 21.03.17
 * Time: 13:46
 */
 class ExploreController implements Controller {
     private $twig;
     private $dataProvider;
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
      * ExploreController constructor.
      * @param Twig_Environment $twig Twig environment for loading templates.
      * @param Pagination $pagination Pagination provider.
      * @param BookingsProvider $dataProvider Provider for the data to display.
      * @param ConfigProvider $config Configuration provider.
      * @internal param ConfigProvider $config Configuration provider.
      */
     public function __construct(Twig_Environment $twig, Pagination $pagination, BookingsProvider $dataProvider, ConfigProvider $config)
     {
         $this->twig = $twig;
         $this->dataProvider = $dataProvider;
         $this->pagination = $pagination;
         $this->config = $config;
     }


     /**
      * Returns the code to render.
      * @return string Code to render.
      */
     public function render()
     {
         $data = $this->dataProvider->getSubset($this->pagination->getCurrentPageFirstItemIndex(), $this->pagination->getPageSize());
         $template = $this->twig->load('explore.twig');
         return $template->render(array(
             'bookings' => $data,
             'view' => 'explore',
             'currentPage' => $this->pagination->getCurrentPage(),
             'pageCount' => $this->pagination->getPageCount(),
             'paginationWindow' => $this->config->get('paginationWindow'),
             'fieldTitels' => $this->config->get('fieldNameMapping'),
             'buttonConfigs' => [new ButtonConfig($this->config->get('filterButtonTitle'), 'apply')],
             '_GET' => $_GET,
            ));
     }
 }