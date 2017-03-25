<?php
require_once dirname(__DIR__) . '/Business/AprioriAlgorithm.php';
require_once dirname(__DIR__) . '/Business/BookingsProvider.php';
require_once dirname(__DIR__) . '/Models/Booking.php';
require_once dirname(__DIR__) . '/Models/Histograms.php';
require_once dirname(__DIR__) . '/Models/Histogram.php';
require_once dirname(__DIR__) . '/Models/HistogramBin.php';
require_once dirname(__DIR__) . '/Models/Price.php';
require_once dirname(__DIR__) . '/Models/Distance.php';
require_once dirname(__DIR__) . '/Models/DataTypeCluster.php';
require_once dirname(__DIR__) . '/Models/Field.php';
require_once dirname(__DIR__) . '/Models/IntegerField.php';
require_once dirname(__DIR__) . '/Models/BooleanField.php';
require_once dirname(__DIR__) . '/Models/FloatField.php';
require_once dirname(__DIR__) . '/Models/StringField.php';
require_once dirname(__DIR__) . '/Models/PriceField.php';
require_once dirname(__DIR__) . '/Models/DistanceField.php';
require_once dirname(__DIR__) . '/Utilities/ConfigProvider.php';

use PHPUnit\Framework\TestCase;

class AprioriAlgorithmTest extends TestCase
{
    private $configMock;

    private function GetBooking($intRooms, $intBedrooms, $intStars, $boolTv, $boolBbq, $boolPets,
                                $boolBalcony, $boolSauna, $floatLong, $floatLat, $price, $diSea, $diLake, $diSki)
    {
        return new Booking(1, new DataTypeCluster(
            ['ROOMS' => $intRooms, 'BEDROOMS' => $intBedrooms, 'STARS' => $intStars],
            ['TV' => $boolTv, 'BBQ' => $boolBbq, 'PETS' => $boolPets, 'BALCONY' => $boolBalcony, 'SAUNA' => $boolSauna],
            ['long' => $floatLong, 'lat' => $floatLat],
            [],
            ['PRICE' => $price],
            ['SEA' => $diSea, 'LAKE' => $diLake, 'SKI' => $diSki,]));
    }

    protected function setUp()
    {
        $map = array(
            array('aprioriMinSup', 2),
        );
        $this->configMock = $this->createMock(ConfigProvider::class);
        $this->configMock->method('get')
            ->will($this->returnValueMap($map));
    }

    /**
     * @test
     */
    public function checkIntegerInSetSize1() {
        $rooms = 7;

        $bookingsProviderMock = $this->createMock(BookingsProvider::class);
        $bookingsProviderMock->method('getSubset')
            ->will($this->onConsecutiveCalls([
                $this->GetBooking(
                    $rooms, 1, 1,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    $rooms, 2, 2,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Close, Distance::Empty, Distance::Empty),
            ],[
                $this->GetBooking(
                    $rooms, 3, 3,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),
            ]));
        $bookingsProviderMock->method('hasEndBeenReached')
            ->will($this->onConsecutiveCalls(false,false,true));

        $sut = new AprioriAlgorithm($bookingsProviderMock, $this->configMock);
        $histograms = $sut->run();
        $histogram = $histograms->getHistogram(1);
        $histogramBins = $histogram->getHistogramBins();

        $this->assertEquals(1, count($histogramBins));
        $this->assertEquals(['ROOMS'=>$rooms], $histogramBins[0]->getFields());
        $this->assertEquals(3, $histogramBins[0]->getCount());
    }

    /**
     * @test
     */
    public function checkBooleansInSetSize1() {
        $tv = true;

        $bookingsProviderMock = $this->createMock(BookingsProvider::class);
        $bookingsProviderMock->method('getSubset')
            ->will($this->onConsecutiveCalls([
                $this->GetBooking(
                    1, 1, 1,
                    $tv, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    2, 2, 2,
                    $tv, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),
            ],[
                $this->GetBooking(
                    3, 3, 3,
                    $tv, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),
            ]));
        $bookingsProviderMock->method('hasEndBeenReached')
            ->will($this->onConsecutiveCalls(false,false,true));

        $sut = new AprioriAlgorithm($bookingsProviderMock, $this->configMock);
        $histograms = $sut->run();
        $histogram = $histograms->getHistogram(1);
        $histogramBins = $histogram->getHistogramBins();

        $this->assertEquals(1, count($histogramBins));
        $this->assertEquals(['TV'=>$tv], $histogramBins[0]->getFields());
        $this->assertEquals(3, $histogramBins[0]->getCount());
    }

    /**
     * @test
     */
    public function checkPriceInSetSize1() {
        $price = Price::Budget;

        $bookingsProviderMock = $this->createMock(BookingsProvider::class);
        $bookingsProviderMock->method('getSubset')
            ->will($this->onConsecutiveCalls([
                $this->GetBooking(
                    1, 1, 1,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    $price,
                    Distance::Empty, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    2, 2, 2,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    $price,
                    Distance::Empty, Distance::Empty, Distance::Empty),
            ],[
                $this->GetBooking(
                    3, 3, 3,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    $price,
                    Distance::Empty, Distance::Empty, Distance::Empty),
            ]));
        $bookingsProviderMock->method('hasEndBeenReached')
            ->will($this->onConsecutiveCalls(false,false,true));

        $sut = new AprioriAlgorithm($bookingsProviderMock, $this->configMock);
        $histograms = $sut->run();
        $histogram = $histograms->getHistogram(1);
        $histogramBins = $histogram->getHistogramBins();

        $this->assertEquals(1, count($histogramBins));
        $this->assertEquals(['PRICE'=>$price], $histogramBins[0]->getFields());
        $this->assertEquals(3, $histogramBins[0]->getCount());
    }

    /**
     * @test
     */
    public function checkDistancesithNoFilterInSetSize1() {
        $diSea = Distance::Close;

        $bookingsProviderMock = $this->createMock(BookingsProvider::class);
        $bookingsProviderMock->method('getSubset')
            ->will($this->onConsecutiveCalls([
                $this->GetBooking(
                    1, 1, 1,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    $diSea, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    2, 2, 2,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    $diSea, Distance::Empty, Distance::Empty),
            ],[
                $this->GetBooking(
                    3, 3, 3,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    $diSea, Distance::Empty, Distance::Empty),
            ]));
        $bookingsProviderMock->method('hasEndBeenReached')
            ->will($this->onConsecutiveCalls(false,false,true));

        $sut = new AprioriAlgorithm($bookingsProviderMock, $this->configMock);
        $histograms = $sut->run();
        $histogram = $histograms->getHistogram(1);
        $histogramBins = $histogram->getHistogramBins();

        $this->assertEquals(1, count($histogramBins));
        $this->assertEquals(['SEA'=>$diSea], $histogramBins[0]->getFields());
        $this->assertEquals(3, $histogramBins[0]->getCount());
    }

    /**
     * @test
     */
    public function checkIntegerInSetSize2() {
        $bookingsProviderMock = $this->createMock(BookingsProvider::class);
        $bookingsProviderMock->method('getSubset')
            ->will($this->onConsecutiveCalls([
                $this->GetBooking(
                    7, 8, 1,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    7, 8, 2,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Close, Distance::Empty, Distance::Empty),
            ],[
                $this->GetBooking(
                    7, 8, 3,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),
            ]));
        $bookingsProviderMock->method('hasEndBeenReached')
            ->will($this->onConsecutiveCalls(false,false,true));

        $sut = new AprioriAlgorithm($bookingsProviderMock, $this->configMock);
        $histograms = $sut->run();
        $histogram = $histograms->getHistogram(2);
        $histogramBins = $histogram->getHistogramBins();

        $this->assertEquals(1, count($histogramBins));
        $this->assertEquals(['ROOMS' => 7,'BEDROOMS' => 8], $histogramBins[0]->getFields());
        $this->assertEquals(3, $histogramBins[0]->getCount());
    }
}
