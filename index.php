<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
//require_once __DIR__ . '/Utilities/Autoloader.php';
require_once __DIR__ . '/Interfaces/BookingDataIterator.php';
require_once __DIR__ . '/Interfaces/Controller.php';
require_once __DIR__ . '/Interfaces/Field.php';
require_once __DIR__ . '/Business/AprioriAlgorithm.php';
require_once __DIR__ . '/Business/BookingsProvider.php';
require_once __DIR__ . '/Business/DataTypeClusterer.php';
require_once __DIR__ . '/Business/FiltersProvider.php';
require_once __DIR__ . '/Business/Pagination.php';
require_once __DIR__ . '/Controllers/AttributanalysisController.php';
require_once __DIR__ . '/Controllers/ExploreController.php';
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
require_once __DIR__ . '/Utilities/LoadAllCsvDataIterator.php';
require_once __DIR__ . '/Utilities/LoadIncrementalCsvDataIterator.php';
require_once __DIR__ . '/Utilities/UrlGenerator.php';

//spl_autoload_register(function ($classname) {
//    Autoloader::load($classname);
//});

/* CONFIG */
$config = new ConfigProvider($GLOBALS['configContent']);
$config->set('rootDir', __DIR__);

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
$builder->addDefinitions(array(
    Twig_Environment::class => $twig,
    BookingDataIterator::class => new LoadIncrementalCsvDataIterator($config->get('dataSource')),
    //BookingDataIterator::class => new LoadAllCsvDataIterator($config->get('dataSource')),
    ConfigProvider::class => $config
));
$container = $builder->build();

/* CONTROLLER */
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





function sortHistogramBinsByCount($histogramBins) {
    usort($histogramBins, function(HistogramBin $a, HistogramBin $b) {
        $aCount = $a->getCount();
        $bCount = $b->getCount();
        if ($aCount == $bCount) {
            return 0;
        }
        return $aCount > $bCount ? -1 : 1;
    });
    return $histogramBins;
}