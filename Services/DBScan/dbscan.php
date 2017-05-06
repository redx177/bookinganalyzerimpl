<?php
$rootDir = dirname(dirname(__DIR__));
require_once $rootDir . '/vendor/autoload.php';
require_once $rootDir . '/config.php';
require_once $rootDir . '/Interfaces/DataIterator.php';
require_once $rootDir . '/Interfaces/AprioriProgress.php';
require_once $rootDir . '/Interfaces/Field.php';
require_once $rootDir . '/Interfaces/Random.php';
require_once $rootDir . '/Interfaces/AprioriProgress.php';
require_once $rootDir . '/Interfaces/ClusteringResult.php';
require_once $rootDir . '/Interfaces/Cluster.php';
require_once $rootDir . '/Utilities/ConfigProvider.php';
require_once $rootDir . '/Utilities/LoadAllCsvDataIterator.php';
require_once $rootDir . '/Utilities/LoadIncrementalCsvDataIterator.php';
require_once $rootDir . '/Utilities/LoadClusterDataIterator.php';
require_once $rootDir . '/Utilities/LoadRedisDataIterator.php';
require_once $rootDir . '/Utilities/Randomizer.php';
require_once $rootDir . '/Utilities/Runtime.php';
require_once $rootDir . '/Business/AprioriAlgorithm.php';
require_once $rootDir . '/Business/AprioriProgressToFile.php';
require_once $rootDir . '/Business/ClusteringProgress.php';
require_once $rootDir . '/Business/BookingsProvider.php';
require_once $rootDir . '/Business/DataTypeClusterer.php';
require_once $rootDir . '/Business/Pagination.php';
require_once $rootDir . '/Business/FiltersProvider.php';
require_once $rootDir . '/Business/DistanceMeasurement.php';
require_once $rootDir . '/Business/DataCache.php';
require_once $rootDir . '/Business/BookingDataIterator.php';
require_once $rootDir . '/Business/BookingBuilder.php';
require_once $rootDir . '/Business/DBScanClusteringProgress.php';
require_once $rootDir . '/Business/DBScanAlgorithm.php';
require_once $rootDir . '/Models/AprioriState.php';
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
require_once $rootDir . '/Models/DBScanResult.php';
require_once $rootDir . '/Models/ClusterPoint.php';
require_once $rootDir . '/Models/DBScanCluster.php';
$config = new ConfigProvider($GLOBALS['configContent'], $rootDir);
$dbscanConfig = $config->get('dbscan');

/* TWIG */
$loader = new Twig_Loader_Filesystem($rootDir . '/Templates');
$twig = new Twig_Environment($loader, array(
    'debug' => true,
    //'cache' => __DIR__ . '/compilation_cache',
));
$twig->addFunction(new Twig_Function('sortHistogramBinsByCount', 'sortHistogramBinsByCount'));
$twig->addExtension(new Twig_Extension_Debug());
$template = $twig->load('dbscanClusters.twig');

/* REDIS */
$redis = new Redis();
$redis->connect('127.0.0.1');

/* DI CONTAINER */
$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
    Twig_Environment::class => $twig,
    Twig_TemplateWrapper::class => $template,
    ConfigProvider::class => $config,
    Redis::class => $redis,
    Random::class => \DI\object(Randomizer::class),
    BookingDataIterator::class => \DI\object(BookingDataIterator::class)
        ->scope(\DI\Scope::PROTOTYPE),
    FiltersProvider::class => DI\object()
        ->constructorParameter('destinationFile', $config->get('rootDir') . '/' . $config->get('destinationFile')),

    // Scope::PROTOTYPE is set so it creates a new instance everytime.
    DataIterator::class => \DI\factory(function () use ($rootDir, $config) {
        return new LoadIncrementalCsvDataIterator($config,$rootDir . '/' . $config->get('dataSource'));
    })->scope(\DI\Scope::PROTOTYPE),

    // Create new instance here. It will start tracking time from the point of instantiation.
    Runtime::class => new Runtime(),

    'clusteringConfig' => \DI\value($dbscanConfig),
    AprioriProgress::class => \DI\object(AprioriProgressToFile::class)
        ->constructor(
            \DI\get(ConfigProvider::class),
            \DI\get(Twig_Environment::class),
            \DI\get(Runtime::class),
            \DI\get('clusteringConfig'),
            \DI\get(Twig_TemplateWrapper::class)),
]);
$container = $builder->build();

// Get a DBScanClusteringProgress class here. It will start to keep track of timings as soon as it instantiated.
$container->set(ClusteringProgress::class, $container->get(DBScanClusteringProgress::class));

if (array_key_exists('abort', $_GET) && $_GET['abort']) {
    file_put_contents($rootDir . $dbscanConfig['serviceStopFile'], "");
} else {
    /** @var DBScanAlgorithm $dbscan */
    /** @var FiltersProvider $filtersProvider */
    /** @var DataCache $cache */
    /** @var AprioriAlgorithm $apriori */

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

    // Run k-prototype
    unlink($rootDir . $dbscanConfig['serviceStopFile']);
    $dbscan = $container->get(DBScanAlgorithm::class);
    $clusters = $dbscan->run();

    // Run apirori
    $apriori = $container->get(AprioriAlgorithm::class);
    $apriori->runWithClusters($clusters);

    unlink($rootDir . $dbscanConfig['servicePidFile']);
}