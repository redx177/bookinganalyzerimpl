<?php

require_once dirname(__DIR__) . '/Business/DistanceMeasurement.php';
require_once dirname(__DIR__) . '/Business/DataTypeClusterer.php';
require_once dirname(__DIR__) . '/Interfaces/Field.php';
require_once dirname(__DIR__) . '/Models/Cluster.php';
require_once dirname(__DIR__) . '/Models/Booking.php';
require_once dirname(__DIR__) . '/Models/DataTypeCluster.php';
require_once dirname(__DIR__) . '/Models/IntegerField.php';
require_once dirname(__DIR__) . '/Models/BooleanField.php';
require_once dirname(__DIR__) . '/Models/FloatField.php';
require_once dirname(__DIR__) . '/Models/StringField.php';
require_once dirname(__DIR__) . '/Models/DistanceField.php';
require_once dirname(__DIR__) . '/Models/PriceField.php';
require_once dirname(__DIR__) . '/Models/Price.php';
require_once dirname(__DIR__) . '/Models/Distance.php';
require_once dirname(__DIR__) . '/Utilities/ConfigProvider.php';

use PHPUnit\Framework\TestCase;

class DistanceMeasurementTest extends TestCase
{
    /**
     * @var Booking
     */
    private $center;
    /**
     * @var ConfigProvider
     */
    private $configMock;

    protected function setUp()
    {
        $this->center = new Booking(1, $this->getDataTypeCluster());

        $map = [
            ['kprototype', ['gamma' => 1]],
        ];
        $this->configMock = $this->createMock(ConfigProvider::class);
        $this->configMock->method('get')->will($this->returnValueMap($map));
    }

    private function getDataTypeCluster($int1 = 4, $int2 = 5, $bool1 = false, $bool2 = true, $pri1 = Price::Empty, $dist1 = Distance::Empty): DataTypeCluster
    {
        return new DataTypeCluster(
            ['int' => new IntegerField('int', $int1), 'int2' => new IntegerField('int2', $int2)],
            ['bool1' => new BooleanField('bool1', $bool1), 'bool2' => new BooleanField('bool2', $bool2)],
            ['float' => new FloatField('float', 2.20)],
            ['str' => new StringField('str', 'd')],
            ['pri' => new PriceField('pri', $pri1)],
            ['dist' => new DistanceField('dist', $dist1)]);
    }

    /**
     * @test
     */
    public function sameBookingsShouldReturn0Distance() {
        $sut = new DistanceMeasurement($this->configMock);
        $distance = $sut->measure($this->center, $this->center);
        $this->assertEquals(0, $distance);
    }

    /**
     * @test
     */
    public function twoDifferenceInIntFieldShouldReturn4DifferenceInDistance() {
        $booking = new Booking(1, $this->getDataTypeCluster(6));

        $sut = new DistanceMeasurement($this->configMock);
        $distance = $sut->measure($this->center, $booking);
        $this->assertEquals(4, $distance);
    }

    /**
     * @test
     */
    public function oneIntDifference2SmallerAndAnother2GreaterShouldReturnLargerDistance() {
        $booking = new Booking(1, $this->getDataTypeCluster(6, 3));

        $sut = new DistanceMeasurement($this->configMock);
        $distance = $sut->measure($this->center, $booking);
        $this->assertEquals(8, $distance);
    }

    /**
     * @test
     */
    public function oneBoolDifferenceShouldReturnDistanceOf1() {
        $booking = new Booking(1, $this->getDataTypeCluster(4, 5, true));

        $sut = new DistanceMeasurement($this->configMock);
        $distance = $sut->measure($this->center, $booking);
        $this->assertEquals(1, $distance);
    }

    /**
     * @test
     */
    public function twoBoolDifferenceShouldReturnDistanceOf2() {
        $booking = new Booking(1, $this->getDataTypeCluster(4, 5, true, false));

        $sut = new DistanceMeasurement($this->configMock);
        $distance = $sut->measure($this->center, $booking);
        $this->assertEquals(2, $distance);
    }

    /**
     * @test
     */
    public function priceBudgetDifferenceShouldReturnDistanceOf1() {
        $booking = new Booking(1, $this->getDataTypeCluster(4, 5, false, true, Price::Budget));

        $sut = new DistanceMeasurement($this->configMock);
        $distance = $sut->measure($this->center, $booking);
        $this->assertEquals(1, $distance);
    }

    /**
     * @test
     */
    public function priceLuxuryDifferenceShouldReturnDistanceOf1() {
        $booking = new Booking(1, $this->getDataTypeCluster(4, 5, false, true, Price::Luxury));

        $sut = new DistanceMeasurement($this->configMock);
        $distance = $sut->measure($this->center, $booking);
        $this->assertEquals(1, $distance);
    }

    /**
     * @test
     */
    public function distanceCloseDifferenceShouldReturnDistanceOf1() {
        $booking = new Booking(1, $this->getDataTypeCluster(4, 5, false, true, Price::Empty, Distance::Close));

        $sut = new DistanceMeasurement($this->configMock);
        $distance = $sut->measure($this->center, $booking);
        $this->assertEquals(1, $distance);
    }

    /**
     * @test
     */
    public function gammaShouldReduceCategoricalDistance() {
        $gamma = 0.5;
        $map = [
            ['kprototype', ['gamma' => $gamma]],
        ];
        $configMock = $this->createMock(ConfigProvider::class);
        $configMock->method('get')
            ->will($this->returnValueMap($map));

        $booking = new Booking(1, $this->getDataTypeCluster(6, 3, true, false));

        $sut = new DistanceMeasurement($configMock);
        $distance = $sut->measure($this->center, $booking);
        $this->assertEquals((4+4) + $gamma*(1+1), $distance);
    }

    /**
     * @test
     */
    public function gammaShouldEnlargeCategoricalDistance() {
        $gamma = 2;
        $map = [
            ['kprototype', ['gamma' => $gamma]],
        ];
        $configMock = $this->createMock(ConfigProvider::class);
        $configMock->method('get')
            ->will($this->returnValueMap($map));

        $booking = new Booking(1, $this->getDataTypeCluster(6, 3, true, false));

        $sut = new DistanceMeasurement($configMock);
        $distance = $sut->measure($this->center, $booking);
        $this->assertEquals((4+4) + $gamma*(1+1), $distance);
    }
}