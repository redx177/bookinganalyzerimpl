<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
//require_once __DIR__ . '/Utilities/Autoloader.php';
require_once __DIR__ . '/Interfaces/DataIterator.php';
require_once __DIR__ . '/Interfaces/Controller.php';
require_once __DIR__ . '/Interfaces/Field.php';
require_once __DIR__ . '/Business/Algorithms/AprioriAlgorithm.php';
require_once __DIR__ . '/Business/BookingsProvider.php';
require_once __DIR__ . '/Business/DataTypeClusterer.php';
require_once __DIR__ . '/Business/FiltersProvider.php';
require_once __DIR__ . '/Business/DataCache.php';
require_once __DIR__ . '/Business/BookingDataIterator.php';
require_once __DIR__ . '/Business/BookingBuilder.php';
require_once __DIR__ . '/Controllers/ExploreController.php';
require_once __DIR__ . '/Controllers/AttributanalysisController.php';
require_once __DIR__ . '/Controllers/AttributanalysisWithGroupingController.php';
require_once __DIR__ . '/Controllers/SettingsController.php';
require_once __DIR__ . '/Models/Booking.php';
require_once __DIR__ . '/Models/BooleanField.php';
require_once __DIR__ . '/Models/ButtonConfig.php';
require_once __DIR__ . '/Models/DataTypeCluster.php';
require_once __DIR__ . '/Models/Distance.php';
require_once __DIR__ . '/Models/DistanceField.php';
require_once __DIR__ . '/Models/Filter.php';
require_once __DIR__ . '/Models/Filters.php';
require_once __DIR__ . '/Models/FloatField.php';
require_once __DIR__ . '/Models/Histogram.php';
require_once __DIR__ . '/Models/HistogramBin.php';
require_once __DIR__ . '/Models/Histograms.php';
require_once __DIR__ . '/Models/IntegerField.php';
require_once __DIR__ . '/Models/Price.php';
require_once __DIR__ . '/Models/PriceField.php';
require_once __DIR__ . '/Models/StringField.php';
require_once __DIR__ . '/Utilities/ConfigProvider.php';
require_once __DIR__ . '/Utilities/Iterators/LoadAllCsvDataIterator.php';
require_once __DIR__ . '/Utilities/Iterators/LoadIncrementalCsvDataIterator.php';
require_once __DIR__ . '/Utilities/UrlGenerator.php';
require_once __DIR__ . '/Utilities/Pagination.php';

/* CONFIG */
$config = new ConfigProvider($GLOBALS['configContent'], __DIR__);

/* TWIG */
$loader = new Twig_Loader_Filesystem(__DIR__ . '/Templates');
$twig = new Twig_Environment($loader, array(
    'debug' => true,
    //'cache' => __DIR__ . '/compilation_cache',
));
$twig->addFunction(new Twig_Function('sortHistogramBinsByCount', 'sortHistogramBinsByCount'));
$twig->addExtension(new Twig_Extension_Debug());

/* DI CONTAINER */
$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
    Twig_Environment::class => $twig,
    DataIterator::class => new LoadIncrementalCsvDataIterator($config, $config->get('dataSource')),
    //BookingDataIterator::class => new LoadAllCsvDataIterator($config->get('dataSource')),
    ConfigProvider::class => $config,
    FiltersProvider::class => DI\object()
        ->constructorParameter('destinationFile', $config->get('rootDir') . '/' . $config->get('destinationFile'))
        ->constructorParameter('customerDestinationFile', $config->get('rootDir') . '/' . $config->get('customerDestinationFile')),
]);
$container = $builder->build();

/* FILTERS */
/** @var Filters $filters */
$filters = $container->get('FiltersProvider')->get($_REQUEST);
$container->set(Filters::class, $filters);

/* FILE CACHE */
/** @var DataCache $cache */
$cache = $container->get(DataCache::class);
$cacheFile = $cache->getCacheFile($filters);
$countFile = $cache->getCountFile($filters);
$container->set(DataIterator::class, new LoadIncrementalCsvDataIterator($config, $cacheFile, $countFile));

/* CONTROLLER */
/** @var Controller $controller */
$controller = null;
$getKeys = array_keys($_REQUEST);
if (in_array('attributanalysis', $getKeys)) {
    $controller = $container->get(AttributanalysisController::class);
} elseif (in_array('attributanalysisWithGrouping', $getKeys)) {
    $controller = $container->get(AttributanalysisWithGroupingController::class);
} elseif (in_array('settings', $getKeys)) {
    $controller = $container->get(SettingsController::class);
} else {
    $controller = $container->get(ExploreController::class);
}

echo $controller->render();


function sortHistogramBinsByCount($histogramBins)
{
    usort($histogramBins, function (HistogramBin $a, HistogramBin $b) {
        $aCount = $a->getCount();
        $bCount = $b->getCount();
        if ($aCount == $bCount) {
            return 0;
        }
        return $aCount > $bCount ? -1 : 1;
    });
    return $histogramBins;
}