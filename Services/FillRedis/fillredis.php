<?php

$rootDir = dirname(dirname(__DIR__));
require_once $rootDir . '/vendor/autoload.php';
require_once $rootDir . '/config.php';
require_once $rootDir . '/Utilities/Autoloader.php';

/* CONFIG */
$config = new ConfigProvider($GLOBALS['configContent'], $rootDir);
$config->set('rootDir', $rootDir);

$bookingsProvider = new BookingsProvider(
    new BookingDataIterator(
        new LoadIncrementalCsvDataIterator($config, $rootDir . '/' . $config->get('dataSource')),
        new BookingBuilder($config, new DataTypeClusterer($config)))
);

$redis = new Redis();
$redis->connect('127.0.0.1');
$redis->flushAll();
$i = 0;
$batchSize = 1000;


$startTime = microtime(TRUE);
echo "Adding bookings to redis:\n";
while (!$bookingsProvider->hasEndBeenReached()) {
    $bookings = $bookingsProvider->getSubset($batchSize);
    foreach ($bookings as $booking) {
        $id = $booking->getId();
        $redis->hSet($id, 'id', $id);
        foreach ($booking->getFields() as $field) {
            $value = null;
            if ($field->getType() == DistanceField::class) {
                $value = getParamsForEnums($field, Distance::class);
            } else if ($field->getType() == PriceField::class) {
                $value = getParamsForEnums($field, Price::class);
            } else {
                $value = $field->getValue();
            }
            $redis->hSet($id, $field->getName(), $value);
        }
        $i++;
    }
    echo "- {$i}\n";
}
$redis->set('bookingsCount', $i);
echo "Setting bookings count to: {$i}\n";
$endtime = microtime(TRUE);
$runtime = $endtime - $startTime;
echo "Runtime: {$runtime}\n";

function getParamsForEnums($field, $className)
{
    $rawValue = $field->getValue();
    $class = new ReflectionClass($className);
    foreach ($class->getConstants() as $name => $value) {
        if ($value > 0 && $value === $rawValue) {
            return strtolower($name);
        }
    }

    return '';
}