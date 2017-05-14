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
$destinations = $filtersProvider->getDestinations();

$done = [];
$result = [];
$term = strtolower($_GET['term']);
foreach ($destinations as $destination) {
    $country = strtolower($destination[0]);
    $region = strtolower($destination[1]);
    $place = strtolower($destination[2]);
    if (strpos($country, $term) !== false && !in_array($country, $done)) {
        $result[] = "{\"label\": \"$destination[0]\", \"country\": \"$destination[0]\", \"region\": \"\", \"place\": \"\", \"category\": \"country\"}";
        $done[] = $country;
    }
    if ((strpos($country, $term) !== false || strpos($region, $term) !== false) && !in_array($country . $region, $done)) {
        $result[] = "{\"label\": \"$destination[0] > $destination[1]\", \"country\": \"$destination[0]\", \"region\": \"$destination[1]\", \"place\": \"\", \"category\": \"region\"}";
        $done[] = $country . $region;
    }
    if (strpos($country, $term) !== false || strpos($region, $term) !== false || strpos($place, $term) !== false) {
        $result[] = "{\"label\": \"$destination[0] > $destination[1] > $destination[2]\", \"country\": \"$destination[0]\", \"region\": \"$destination[1]\", \"place\": \"$destination[2]\", \"category\": \"place\"}";
    }
}
echo '[' . implode(',', $result) . ']';