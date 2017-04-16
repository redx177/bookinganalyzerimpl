<?php
require_once dirname(__DIR__) . '/Business/KPrototypeAlgorithm.php';
require_once dirname(__DIR__) . '/Business/DistanceMeasurement.php';
require_once dirname(__DIR__) . '/Business/BookingsProvider.php';
require_once dirname(__DIR__) . '/Business/DataTypeClusterer.php';
require_once dirname(__DIR__) . '/Interfaces/Random.php';
require_once dirname(__DIR__) . '/Interfaces/Field.php';
require_once dirname(__DIR__) . '/Interfaces/BookingDataIterator.php';
require_once dirname(__DIR__) . '/Models/Price.php';
require_once dirname(__DIR__) . '/Models/Distance.php';
require_once dirname(__DIR__) . '/Models/Booking.php';
require_once dirname(__DIR__) . '/Models/DataTypeCluster.php';
require_once dirname(__DIR__) . '/Models/IntegerField.php';
require_once dirname(__DIR__) . '/Models/BooleanField.php';
require_once dirname(__DIR__) . '/Models/FloatField.php';
require_once dirname(__DIR__) . '/Models/StringField.php';
require_once dirname(__DIR__) . '/Models/DistanceField.php';
require_once dirname(__DIR__) . '/Models/PriceField.php';
require_once dirname(__DIR__) . '/Models/Clusters.php';
require_once dirname(__DIR__) . '/Models/Cluster.php';
require_once dirname(__DIR__) . '/Models/Associate.php';
require_once dirname(__DIR__) . '/Utilities/ConfigProvider.php';
require_once dirname(__DIR__) . '/Utilities/LoadRedisDataIterator.php';

use PHPUnit\Framework\TestCase;

class KPrototypeAlgorithmTest extends TestCase
{
    private $configMock;
    private $distance;
    private $gamma = 1;
    private $bookingsProviderMock;

    private function GetBooking($id, $intRooms, $intBedrooms, $intStars, $boolTv, $boolBbq, $boolPets,
                                $boolBalcony, $boolSauna, $price, $diSea, $diLake, $diSki)
    {
        return new Booking($id, new DataTypeCluster(
            ['ROOMS' => new IntegerField('ROOMS', $intRooms),
                'BEDROOMS' => new IntegerField('BEDROOMS', $intBedrooms),
                'STARS' => new IntegerField('STARS', $intStars)],
            ['TV' => new BooleanField('TV', $boolTv),
                'BBQ' => new BooleanField('BBQ', $boolBbq),
                'PETS' => new BooleanField('PETS', $boolPets),
                'BALCONY' => new BooleanField('BALCONY', $boolBalcony),
                'SAUNA' => new BooleanField('SAUNA', $boolSauna)],
            [],
            [],
            ['PRICE' => new PriceField('PRICE', $price)],
            ['SEA' => new DistanceField('SEA', $diSea),
                'LAKE' => new DistanceField('LAKE', $diLake),
                'SKI' => new DistanceField('SKI', $diSki)]));
    }

    protected function setUp()
    {
        $map = [
            ['kprototype', ['gamma' => $this->gamma,
                'serviceStopFile' => '',
                'outputInterval' => '',
                'serviceOutput' => '']],
        ];
        $this->configMock = $this->createMock(ConfigProvider::class);
        $this->configMock->method('get')->will($this->returnValueMap($map));

        $this->distance = new DistanceMeasurement($this->configMock);

        $hasEndBeenReached = true;
        $this->bookingsProviderMock = $this->createMock(BookingsProvider::class);
        $this->bookingsProviderMock->method('hasEndBeenReached')
            ->will($this->returnCallback(function () use (&$hasEndBeenReached) {
                $hasEndBeenReached = !$hasEndBeenReached;
                return $hasEndBeenReached;
            }));
    }

    public function twoTimesTwoOfTheSameBookingsShouldCreate2ClustersWithNoCosts()
    {

        $center1 = $this->GetBooking(
            1, 1, 1, 1,
            false, false, false, false, false,
            Price::Empty,
            Distance::Empty, Distance::Empty, Distance::Empty);
        $center2 = $this->GetBooking(
            3, 3, 3, 3,
            true, true, true, true, true,
            Price::Luxury,
            Distance::Close, Distance::Close, Distance::Close);
        $this->bookingsProviderMock->method('getSubset')
            ->willReturn([
                // Cluster 1
                $center1,
                $this->GetBooking(
                    2, 1, 1, 1,
                    false, false, false, false, false,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),

                // Cluster 2
                $center2,
                $this->GetBooking(
                    4, 3, 3, 3,
                    true, true, true, true, true,
                    Price::Luxury,
                    Distance::Close, Distance::Close, Distance::Close),
            ]);
        $this->bookingsProviderMock->method('getBooking')
            ->will($this->onConsecutiveCalls($center1, $center2));

        $redisMock = $this->createMock(Redis::class);
        $redisMock->method('get')->willReturn('4');
        $redisMock->method('hGetAll')
            ->will($this->onConsecutiveCalls($center1->getFields(), $center2->getFields()));

        // Random mock is used to set the randomly selected center points.
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('generate')->will($this->onConsecutiveCalls(0, 2));

        $sut = new KPrototypeAlgorithm($this->bookingsProviderMock, $this->configMock, $this->distance, $randomMock, $redisMock);

        $clustersObj = $sut->run();
        $clusters = $clustersObj->getClusters();
        $this->assertEquals(2, count($clusters));

        $this->assertEquals(0, $clusters[0]->getTotalCosts());
        $this->assertEquals(1, $clusters[0]->getCenter()->getId());
        $this->assertEquals(1, count($clusters[0]->getAssociates()));
        $this->assertEquals(2, $clusters[0]->getAssociates()[2]->getId());

        $this->assertEquals(0, $clusters[1]->getTotalCosts());
        $this->assertEquals(3, $clusters[1]->getCenter()->getId());
        $this->assertEquals(1, count($clusters[1]->getAssociates()));
        $this->assertEquals(4, $clusters[1]->getAssociates()[4]->getId());
    }

    public function twoTimesTwoSimilarBookingsShouldCreate2ClustersWithLowCosts()
    {
        $center1 = $this->GetBooking(
            1, 1, 1, 1,
            false, false, false, false, false,
            Price::Empty,
            Distance::Empty, Distance::Empty, Distance::Empty);
        $center2 = $this->GetBooking(
            3, 7, 7, 7,
            true, true, true, true, true,
            Price::Empty,
            Distance::Close, Distance::Close, Distance::Close);
        $associate1 = $this->GetBooking(
            2, 3, 3, 3,
            true, false, false, false, false,
            Price::Budget,
            Distance::Empty, Distance::Empty, Distance::Empty);
        $associate2 = $this->GetBooking(
            4, 9, 9, 9,
            true, true, true, true, true,
            Price::Luxury,
            Distance::Empty, Distance::Empty, Distance::Close);
        $this->bookingsProviderMock->method('getSubset')
            ->willReturn([
                // Cluster 1
                $center1, $associate1,

                // Cluster 2
                $center2, $associate2,
            ]);
        $this->bookingsProviderMock->method('getBooking')
            ->will($this->onConsecutiveCalls($center1, $center2));

        $redisIteratorMock = $this->createMock(LoadRedisDataIterator::class);
        $redisIteratorMock->method('current')
            ->will($this->onConsecutiveCalls($center1->getFields(), $associate1->getFields(), $center2->getFields(), $associate2->getFields()));
        $redisIteratorMock->method('count')->willReturn('4');

        $redisMock = $this->createMock(Redis::class);
        $redisMock->method('hGetAll')
            ->will($this->onConsecutiveCalls($center1->getFields(), $center2->getFields()));


        // Random mock is used to set the randomly selected center points.
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('generate')->will($this->onConsecutiveCalls(0, 2));

        $sut = new KPrototypeAlgorithm($this->bookingsProviderMock, $this->configMock, $this->distance, $randomMock, $redisMock, $redisIteratorMock);

        $clustersObj = $sut->run();
        $clusters = $clustersObj->getClusters();
        $this->assertEquals(2, count($clusters));

        // Total Costs calculation: (integer differences sum of squares) + gamma*(sum of categorical missmatches)
        $this->assertEquals((4+4+4) + $this->gamma*(1+1), $clusters[0]->getTotalCosts());
        $this->assertEquals(1, $clusters[0]->getCenter()->getId());
        $this->assertEquals(1, count($clusters[0]->getAssociates()));
        $this->assertEquals(2, $clusters[0]->getAssociates()[2]->getId());

        $this->assertEquals((4+4+4) + $this->gamma*(1+1+1), $clusters[1]->getTotalCosts());
        $this->assertEquals(3, $clusters[1]->getCenter()->getId());
        $this->assertEquals(1, count($clusters[1]->getAssociates()));
        $this->assertEquals(4, $clusters[1]->getAssociates()[4]->getId());
    }
}
