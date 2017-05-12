<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Business/FiltersProvider.php';
require_once __DIR__ . '/Business/DataTypeClusterer.php';
require_once __DIR__ . '/Utilities/ConfigProvider.php';

$config = new ConfigProvider($GLOBALS['configContent'], __DIR__);
$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
    ConfigProvider::class => $config,
    FiltersProvider::class => DI\object()
        ->constructorParameter('destinationFile', $config->get('rootDir') . '/' . $config->get('destinationFile'))
        ->constructorParameter('customerDestinationFile', $config->get('rootDir') . '/' . $config->get('customerDestinationFile')),
]);
$container = $builder->build();

/** @var FiltersProvider $filtersProvider */
$filtersProvider = $controller = $container->get(FiltersProvider::class);
$destinations = $filtersProvider->getCustomerDestinations();

$done = [];
$result = [];
foreach ($destinations as $destination) {

    if (strpos($destination[0], $_GET['term']) !== false && !in_array($destination[0], $done)) {
        $result[] = "{\"label\": \"$destination[0]\", \"CUCNTRY\": \"$destination[0]\", \"CUORT\": \"\", \"category\": \"CUCNTRY\"}";
        $done[] = $destination[0];
    }
    if (strlen($_GET['term']) < 4) {
        continue;
    }
    if (strpos($destination[0], $_GET['term']) !== false || strpos($destination[1], $_GET['term']) !== false) {
        $result[] = "{\"label\": \"$destination[0] > $destination[1]\", \"CUCNTRY\": \"$destination[0]\", \"CUORT\": \"$destination[1]\", \"category\": \"CUORT\"}";
        $done[] = $destination[0].$destination[1];
    }
}
echo '[' . implode(',', $result) . ']';