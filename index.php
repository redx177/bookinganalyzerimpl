<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Utilities/ConfigProvider.php';
require_once __DIR__ . '/Utilities/CsvIterator.php';
require_once __DIR__ . '/Interfaces/Controller.php';
require_once __DIR__ . '/Controllers/ExploreController.php';
require_once __DIR__ . '/Business/DataProvider.php';

$config = new ConfigProvider();

$loader = new Twig_Loader_Filesystem(__DIR__ . '/Templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => __DIR__ . '/compilation_cache',
));

$builder = new DI\ContainerBuilder();
$builder->addDefinitions(array(
    Twig_Environment::class => $twig,
    CsvIterator::class => new CsvIterator($config->get('dataSource')),
));
$container = $builder->build();


$controller = $container->get('ExploreController');


echo $controller->render();