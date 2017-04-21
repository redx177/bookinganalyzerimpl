<?php
$rootDir = dirname(dirname(__DIR__));
require_once $rootDir . '/vendor/autoload.php';
require_once $rootDir . '/config.php';
require_once $rootDir . '/Interfaces/BookingDataIterator.php';
require_once $rootDir . '/Interfaces/AprioriProgress.php';
require_once $rootDir . '/Interfaces/Field.php';
require_once $rootDir . '/Interfaces/Random.php';
require_once $rootDir . '/Utilities/ConfigProvider.php';
require_once $rootDir . '/Utilities/LoadAllCsvDataIterator.php';
require_once $rootDir . '/Utilities/LoadIncrementalCsvDataIterator.php';
require_once $rootDir . '/Utilities/LoadRedisDataIterator.php';
require_once $rootDir . '/Utilities/Randomizer.php';
require_once $rootDir . '/Business/AprioriAlgorithm.php';
require_once $rootDir . '/Business/AprioriProgressToFile.php';
require_once $rootDir . '/Business/AprioriProgressToMemory.php';
require_once $rootDir . '/Business/ClusteringProgress.php';
require_once $rootDir . '/Business/KPrototypeClusteringProgress.php';
require_once $rootDir . '/Business/BookingsProvider.php';
require_once $rootDir . '/Business/DataTypeClusterer.php';
require_once $rootDir . '/Business/Pagination.php';
require_once $rootDir . '/Business/FiltersProvider.php';
require_once $rootDir . '/Business/KPrototypeAlgorithm.php';
require_once $rootDir . '/Business/DistanceMeasurement.php';
require_once $rootDir . '/Business/DataCache.php';
require_once $rootDir . '/Business/BookingDataIteratorAdapter.php';
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
require_once $rootDir . '/Models/Clusters.php';
require_once $rootDir . '/Models/Cluster.php';
require_once $rootDir . '/Models/Associate.php';
$config = new ConfigProvider($GLOBALS['configContent']);
$config->set('rootDir', $rootDir);
$kprototypeConfig = $config->get('kprototype');

/* TWIG */
$loader = new Twig_Loader_Filesystem($rootDir . '/Templates');
$twig = new Twig_Environment($loader, array(
    'debug' => true,
    //'cache' => __DIR__ . '/compilation_cache',
));
$twig->addFunction(new Twig_Function('sortHistogramBinsByCount', 'sortHistogramBinsByCount'));
$twig->addExtension(new Twig_Extension_Debug());
$template = $twig->load('clusters.twig');

/* REDIS */
$redis = new Redis();
$redis->connect('127.0.0.1');

/* DI CONTAINER */
$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
    Twig_Environment::class => $twig,
    Twig_TemplateWrapper::class => $template,
//    BookingDataIterator::class => new LoadIncrementalCsvDataIterator($rootDir . '/' . $config->get('dataSource')),
    BookingDataIterator::class => \DI\factory(function() use ($rootDir, $config) {
        return new LoadIncrementalCsvDataIterator($rootDir . '/' . $config->get('dataSource'));
    })->scope(\DI\Scope::PROTOTYPE),
    //BookingDataIterator::class => new LoadAllCsvDataIterator($rootDir . '/' . $config->get('dataSource')),
    ConfigProvider::class => $config,
    Twig_TemplateWrapper::class => $template,
    Redis::class => $redis,
    Random::class => new Randomizer(),
    KPrototypeAlgorithm::class => \DI\factory(function($container) use ($rootDir, $config) {
        return new KPrototypeAlgorithm(
            $container->get(ConfigProvider::class),
            $container->get(DistanceMeasurement::class),
            $container->get(Random::class),
            $container->get(Redis::class),
            //new LoadIncrementalCsvDataIterator($rootDir . '/' . $config->get('dataSource')),
            new BookingDataIteratorAdapter($container->make(BookingDataIterator::class), $container->get(BookingBuilder::class)),
            new BookingDataIteratorAdapter($container->make(BookingDataIterator::class), $container->get(BookingBuilder::class)),
            $container->get(ClusteringProgress::class),
            $container->get(BookingBuilder::class)
        );
    })
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
    $cacheFile = $cache->getCacheFile($filters);
    $countFile = $cache->getCountFile($filters);
    $container->set(BookingDataIterator::class, new LoadIncrementalCsvDataIterator($cacheFile, $countFile));

    // Run k-prototype
    unlink($rootDir.$kprototypeConfig['serviceStopFile']);
    $kprototype = $container->get(KPrototypeAlgorithm::class);
    $clusters = $kprototype->run();

    // Run apirori
    $apriori = $container->get(AprioriAlgorithm::class);
    $apriori->runOnClusters($clusters);

    unlink($rootDir.$kprototypeConfig['servicePidFile']);
}