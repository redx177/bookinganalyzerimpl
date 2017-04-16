<?php

$rootDir = dirname(dirname(__DIR__));
require_once $rootDir . '/vendor/autoload.php';
require_once $rootDir . '/config.php';
require_once $rootDir . '/Utilities/Autoloader.php';

/* CONFIG */
$config = new ConfigProvider($GLOBALS['configContent']);
$config->set('rootDir', $rootDir);

$bookingsProvider = new BookingsProvider(
    new LoadIncrementalCsvDataIterator($rootDir . '/' . $config->get('dataSource')),
    new DataTypeClusterer($config),
    $config
);

$redis = new Redis();
$redis->connect('127.0.0.1');
$redis->flushAll();
$i = 0;
$batchSize = 1000;

while (!$bookingsProvider->hasEndBeenReached()) {
    $bookings = $bookingsProvider->getSubset($batchSize);
    foreach ($bookings as $booking) {
        $id = $booking->getId();
        $redis->hSet($id, 'id', $id);
        foreach ($booking->getFields() as $field) {
            $redis->hSet($id, $field->getName(), $field->getValue());
        }
        $i++;
    }
    echo "{$i}\n";
}
$redis->set('bookingsCount', $i);
echo $i;