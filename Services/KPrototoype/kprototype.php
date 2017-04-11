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
require_once $rootDir . '/Utilities/Randomizer.php';
require_once $rootDir . '/Business/AprioriAlgorithm.php';
require_once $rootDir . '/Business/AprioriProgressToFile.php';
require_once $rootDir . '/Business/BookingsProvider.php';
require_once $rootDir . '/Business/DataTypeClusterer.php';
require_once $rootDir . '/Business/Pagination.php';
require_once $rootDir . '/Business/FiltersProvider.php';
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
$config = new ConfigProvider($GLOBALS['configContent']);
$config->set('rootDir', $rootDir);
$aprioriConfig = $config->get('apriori');

/* TWIG */
$loader = new Twig_Loader_Filesystem($rootDir . '/Templates');
$twig = new Twig_Environment($loader, array(
    'debug' => true,
    //'cache' => __DIR__ . '/compilation_cache',
));
$twig->addFunction(new Twig_Function('sortHistogramBinsByCount', 'sortHistogramBinsByCount'));
$twig->addExtension(new Twig_Extension_Debug());
$template = $twig->load('candidatesAndFrequentSetsAsTable.twig');


/* DI CONTAINER */
$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
    Twig_Environment::class => $twig,
    //BookingDataIterator::class => new LoadIncrementalCsvDataIterator($rootDir . '/' . $config->get('dataSource')),
    BookingDataIterator::class => new LoadAllCsvDataIterator($config->get('dataSource')),
    ConfigProvider::class => $config,
    Twig_TemplateWrapper::class => $template,
    AprioriAlgorithm::class => function(\Psr\Container\ContainerInterface $c) use (&$template) {
        return new AprioriAlgorithm(
            $c->get(BookingsProvider::class),
            $c->get(ConfigProvider::class),
            $c->get(AprioriProgressToMemory::class));
    }
]);
$container = $builder->build();
$container->make(AprioriAlgorithm::class, ['template' => $container->get(Twig_TemplateWrapper::class)]);
$container->set(Twig_TemplateWrapper::class, \DI\object(Twig_TemplateWrapper::class));

if (array_key_exists('abort', $_GET) && $_GET['abort']) {
    file_put_contents($rootDir . $aprioriConfig['serviceStopFile'], "");
} else {
    $filtersProvider = $container->get('FiltersProvider');
    $apriori = $container->get('AprioriAlgorithm');
    parse_str(trim($argv[1], "'"), $params);
    $filters = $filtersProvider->get($params);
    unlink($rootDir.$aprioriConfig['serviceStopFile']);
    $histograms = $apriori->run($filters);
    unlink($rootDir.$aprioriConfig['servicePidFile']);
}


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