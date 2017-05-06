<?php
$rootDir = dirname(dirname(__DIR__));
require_once $rootDir . '/vendor/autoload.php';
require_once $rootDir . '/config.php';
require_once $rootDir . '/Interfaces/DataIterator.php';
require_once $rootDir . '/Interfaces/AprioriProgress.php';
require_once $rootDir . '/Interfaces/Field.php';
require_once $rootDir . '/Utilities/ConfigProvider.php';
require_once $rootDir . '/Utilities/LoadAllCsvDataIterator.php';
require_once $rootDir . '/Utilities/LoadIncrementalCsvDataIterator.php';
require_once $rootDir . '/Utilities/LoadRedisDataIterator.php';
require_once $rootDir . '/Utilities/Runtime.php';
require_once $rootDir . '/Business/AprioriAlgorithm.php';
require_once $rootDir . '/Business/AprioriProgressToFile.php';
require_once $rootDir . '/Business/BookingsProvider.php';
require_once $rootDir . '/Business/DataTypeClusterer.php';
require_once $rootDir . '/Business/Pagination.php';
require_once $rootDir . '/Business/FiltersProvider.php';
require_once $rootDir . '/Business/DataCache.php';
require_once $rootDir . '/Business/BookingDataIterator.php';
require_once $rootDir . '/Business/BookingBuilder.php';
require_once $rootDir . '/Models/Booking.php';
require_once $rootDir . '/Models/BooleanField.php';
require_once $rootDir . '/Models/ButtonConfig.php';
require_once $rootDir . '/Models/DataTypeCluster.php';
require_once $rootDir . '/Models/Distance.php';
require_once $rootDir . '/Models/DistanceField.php';
require_once $rootDir . '/Models/Filter.php';
require_once $rootDir . '/Models/Filters.php';
require_once $rootDir . '/Models/FloatField.php';
require_once $rootDir . '/Models/Histogram.php';
require_once $rootDir . '/Models/HistogramBin.php';
require_once $rootDir . '/Models/Histograms.php';
require_once $rootDir . '/Models/IntegerField.php';
require_once $rootDir . '/Models/Price.php';
require_once $rootDir . '/Models/PriceField.php';
require_once $rootDir . '/Models/StringField.php';
$config = new ConfigProvider($GLOBALS['configContent'], $rootDir);
$aprioriConfig = $config->get('apriori');

/* TWIG */
$loader = new Twig_Loader_Filesystem($rootDir . '/Templates');
$twig = new Twig_Environment($loader, array(
    'debug' => true,
    //'cache' => __DIR__ . '/compilation_cache',
));
$twig->addFunction(new Twig_Function('sortHistogramBinsByCount', 'sortHistogramBinsByCount'));
$twig->addExtension(new Twig_Extension_Debug());

/* REDIS */
$redis = new Redis();
$redis->connect('127.0.0.1');


/* DI CONTAINER */
$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
    Twig_Environment::class => $twig,
    ConfigProvider::class => $config,
    Redis::class => $redis,
    FiltersProvider::class => DI\object()
        ->constructorParameter('destinationFile', $config->get('rootDir') . '/' . $config->get('destinationFile')),
    //BookingDataIterator::class => new LoadRedisDataIterator($redis),
    DataIterator::class => new LoadIncrementalCsvDataIterator($config, $rootDir . '/' . $config->get('dataSource')),
    //BookingDataIterator::class => new LoadAllCsvDataIterator($rootDir . '/' . $config->get('dataSource')),
    AprioriProgress::class => \DI\object(AprioriProgressToFile::class)
        ->constructorParameter('clusteringConfig', $kprototypeConfig),

    // Create new instance here. It will start tracking time from the point of instantiation.
    Runtime::class => new Runtime(),
]);
$container = $builder->build();

if (array_key_exists('abort', $_GET) && $_GET['abort']) {
    file_put_contents($rootDir . $aprioriConfig['serviceStopFile'], "");
} else {
    /** @var FiltersProvider $filtersProvider */
    /** @var AprioriAlgorithm $apriori */
    /** @var DataCache $cache */

    // Get filters
    $args = '';
    if (array_key_exists(1, $argv)) {
        $args = $argv[1];
    }
    $filtersProvider = $container->get('FiltersProvider');
    parse_str(trim($args, "'"), $params);
    $filters = $filtersProvider->get($params);

    // Cache file
    $cache = $container->get(DataCache::class);
    $container->set('dataFile', \DI\value($cache->getCacheFile($filters)));
    $container->set('countFile', \DI\value($cache->getCountFile($filters)));
    $container->set(DataIterator::class, \DI\object(LoadIncrementalCsvDataIterator::class)
        ->constructor(\DI\get(ConfigProvider::class), \DI\get('dataFile'), \DI\get('countFile'))
        ->scope(\DI\Scope::PROTOTYPE));

    // Run algorithm
    $apriori = $container->get('AprioriAlgorithm');
    unlink($rootDir . $aprioriConfig['serviceStopFile']);
    $apriori->run();
    unlink($rootDir . $aprioriConfig['servicePidFile']);
}


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