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
      * ExploreController constructor.
      * @param Twig_Environment $twig Twig environment for loading templates.
      * @param ConfigProvider $config Configuration provider.
      * @param DataProvider $dataProvider Provider for the data to display.
      */
     public function __construct(Twig_Environment $twig, ConfigProvider $config, DataProvider $dataProvider)
     {
         $this->twig = $twig;
         $this->dataProvider = $dataProvider;
         $this->pageSize = $config->get('pageSize') ? $config->get('pageSize') : 5;
     }


     /**
      * Returns the code to render.
      * @return string Code to render.
      */
     public function render()
     {
         $page = array_key_exists('page', $_GET) ? $_GET['page'] : 1;
         $data = $this->dataProvider->getSubset($page * $this->pageSize, $this->pageSize);
         $template = $this->twig->load('explore.twig', array('pageName' => "Attributanalysis"));
         return $template->render();
     }
 }