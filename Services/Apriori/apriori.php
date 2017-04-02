<?php

//require_once __DIR__ . '/config.php';
//require_once __DIR__ . '/Utilities/ConfigProvider.php';
//require_once __DIR__ . '/Business/AprioriAlgorithm.php';
//require_once __DIR__ . '/Business/BookingsProvider.php';
//require_once __DIR__ . '/Business/DataTypeClusterer.php';
//require_once __DIR__ . '/Business/Pagination.php';
//require_once __DIR__ . '/Models/Field.php';
//require_once __DIR__ . '/Models/Booking.php';
//require_once __DIR__ . '/Models/BooleanField.php';
//require_once __DIR__ . '/Models/ButtonConfig.php';
//require_once __DIR__ . '/Models/DataTypeCluster.php';
//require_once __DIR__ . '/Models/Distance.php';
//require_once __DIR__ . '/Models/DistanceField.php';
//require_once __DIR__ . '/Models/Filter.php';
//require_once __DIR__ . '/Models/Filters.php';
//require_once __DIR__ . '/Models/FloatField.php';
//require_once __DIR__ . '/Models/Histogram.php';
//require_once __DIR__ . '/Models/HistogramBin.php';
//require_once __DIR__ . '/Models/Histograms.php';
//require_once __DIR__ . '/Models/IntegerField.php';
//require_once __DIR__ . '/Models/Price.php';
//require_once __DIR__ . '/Models/PriceField.php';
//require_once __DIR__ . '/Models/StringField.php';
//$config = new ConfigProvider($GLOBALS['configContent']);

use WebSocketClient\WebSocketClient;
use WebSocketClient\WebSocketClientInterface;

class Client implements WebSocketClientInterface
{
    private $client;

    public function onWelcome(array $data)
    {
    }

    public function onEvent($topic, $message)
    {
    }

    public function subscribe($topic)
    {
        $this->client->subscribe($topic);
    }

    public function unsubscribe($topic)
    {
        $this->client->unsubscribe($topic);
    }

    public function call($proc, $args, Closure $callback = null)
    {
        $this->client->call($proc, $args, $callback);
    }

    public function publish($topic, $message)
    {
        $this->client->publish($topic, $message);
    }

    public function setClient(WebSocketClient $client)
    {
        $this->client = $client;
    }
}

$loop = React\EventLoop\Factory::create();

$client = new WebSocketClient(new Client, $loop);

$loop->run();