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
require_once $rootDir . '/Utilities/Iterators/LoadAllCsvDataIterator.php';
require_once $rootDir . '/Utilities/Iterators/LoadIncrementalCsvDataIterator.php';
require_once $rootDir . '/Utilities/Iterators/LoadClusterDataIterator.php';
require_once $rootDir . '/Utilities/Iterators/LoadRedisDataIterator.php';
require_once $rootDir . '/Utilities/Randomizer.php';
require_once $rootDir . '/Utilities/Runtime.php';
require_once $rootDir . '/Business/Algorithms/AprioriAlgorithm.php';
require_once $rootDir . '/Business/Progress/AprioriProgressToFile.php';
require_once $rootDir . '/Business/Progress/KPrototypeProgressToFile.php';
require_once $rootDir . '/Business/Progress/ClusteringProgress.php';
require_once $rootDir . '/Business/Progress/KPrototypeClusteringProgress.php';
require_once $rootDir . '/Business/BookingsProvider.php';
require_once $rootDir . '/Business/DataTypeClusterer.php';
require_once $rootDir . '/Business/FiltersProvider.php';
require_once $rootDir . '/Business/Algorithms/KPrototypeAlgorithm.php';
require_once $rootDir . '/Business/DistanceMeasurement.php';
require_once $rootDir . '/Business/DataCache.php';
require_once $rootDir . '/Business/BookingDataIterator.php';
require_once $rootDir . '/Business/BookingBuilder.php';
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
require_once $rootDir . '/Models/KPrototypeResult.php';
require_once $rootDir . '/Models/KPrototypeCluster.php';
require_once $rootDir . '/Models/ClusterPoint.php';
require_once $rootDir . '/Models/DistanceClusterPoint.php';
$config = new ConfigProvider($GLOBALS['configContent'], $rootDir);
$kprototypeConfig = $config->get('kprototype');

/* TWIG */
$loader = new Twig_Loader_Filesystem($rootDir . '/Templates');
$twig = new Twig_Environment($loader, array(
    'debug' => true,
    //'cache' => __DIR__ . '/compilation_cache',
));
$twig->addFunction(new Twig_Function('sortHistogramBinsByCount', 'sortHistogramBinsByCount'));
$twig->addExtension(new Twig_Extension_Debug());
$template = $twig->load('kprototypeClusters.twig');

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

    AprioriProgress::class => \DI\object(KPrototypeProgressToFile::class)
]);
$container = $builder->build();

// Get a KPrototypeClusteringProcess class here. It will start to keep track of timings as soon as it instantiated.
$container->set(ClusteringProgress::class, $container->get(KPrototypeClusteringProgress::class));

if (array_key_exists('abort', $_GET) && $_GET['abort']) {
    file_put_contents($rootDir . $kprototypeConfig['serviceStopFile'], "");
} else {
    /** @var KPrototypeAlgorithm $kprototype */
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
    unlink($rootDir . $kprototypeConfig['serviceStopFile']);
    $kprototype = $container->get(KPrototypeAlgorithm::class);
    $clusters = $kprototype->run();

    // Run apirori
    $apriori = $container->get(AprioriAlgorithm::class);
    $apriori->runWithClusters($clusters);

    unlink($rootDir . $kprototypeConfig['servicePidFile']);
}