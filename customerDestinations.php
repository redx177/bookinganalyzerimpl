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
$term = strtolower($_GET['term']);
foreach ($destinations as $destination) {

    $country = strtolower($destination[0]);
    if (strpos($country, $term) !== false && !in_array($country, $done)) {
        $result[] = "{\"label\": \"$destination[0]\", \"CUCNTRY\": \"$destination[0]\", \"CUORT\": \"\", \"category\": \"CUCNTRY\"}";
        $done[] = $country;
    }
    if (strlen($term) < 4) {
        continue;
    }
    $place = strtolower($destination[1]);
    if ((strpos($country, $term) !== false || strpos($place, $term) !== false)  && !in_array($country . $place, $done)) {
        $result[] = "{\"label\": \"$destination[0] > $destination[1]\", \"CUCNTRY\": \"$destination[0]\", \"CUORT\": \"$destination[1]\", \"category\": \"CUORT\"}";
        $done[] = $country . $place;
    }
}
echo '[' . implode(',', $result) . ']';