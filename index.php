<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Utilities/Autoloader.php';

spl_autoload_register(function ($classname) {
    Autoloader::load($classname);
});

$config = new ConfigProvider($GLOBALS['configContent']);

$loader = new Twig_Loader_Filesystem(__DIR__ . '/Templates');
$twig = new Twig_Environment($loader, array(
    'debug' => true,
    //'cache' => __DIR__ . '/compilation_cache',
));
$twig->addExtension(new Twig_Extension_Debug());

$builder = new DI\ContainerBuilder();
$builder->addDefinitions(array(
    Twig_Environment::class => $twig,
    CsvIterator::class => new CsvIterator($config->get('dataSource')),
    ConfigProvider::class => $config
));
$container = $builder->build();

$controller = null;
$getKeys = array_keys($_REQUEST);
if (in_array('attributanalysis', $getKeys)) {
    $controller = $container->get('AttributanalysisController');
} elseif (in_array('attributanalysiswithgrouping', $getKeys)) {
    $controller = $container->get('AttributanalysisWithGroupingController');
} elseif (in_array('settings', $getKeys)) {
    $controller = $container->get('SettingsController');
} else {
    $controller = $container->get('ExploreController');
}

echo $controller->render();