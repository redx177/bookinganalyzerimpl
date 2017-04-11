<?php
require_once dirname(__DIR__) . '/Business/KPrototypeAlgorithm.php';
require_once dirname(__DIR__) . '/Business/DistanceMeasurement.php';
require_once dirname(__DIR__) . '/Business/BookingsProvider.php';
require_once dirname(__DIR__) . '/Business/DataTypeClusterer.php';
require_once dirname(__DIR__) . '/Interfaces/Random.php';
require_once dirname(__DIR__) . '/Interfaces/Field.php';
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
require_once dirname(__DIR__) . '/Models/Cluster.php';
require_once dirname(__DIR__) . '/Models/Associate.php';
require_once dirname(__DIR__) . '/Utilities/ConfigProvider.php';

use PHPUnit\Framework\TestCase;

class KPrototypeAlgorithmTest extends TestCase
{
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

    /**
     * @test
     */
    public function getClusters()
    {

        $bookingsProviderMock = $this->createMock(BookingsProvider::class);
        $bookingsProviderMock->method('getSubset')
            ->willReturn([
                // Cluster 1
                $this->GetBooking(
                    1, 1, 1, 1,
                    false, false, false, false, false,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    2, 1, 1, 1,
                    false, false, false, false, false,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),

                // Cluster 2
                $this->GetBooking(
                    3, 3, 3, 3,
                    true, true, true, true, true,
                    Price::Luxury,
                    Distance::Close, Distance::Close, Distance::Close),
                $this->GetBooking(
                    4, 3, 3, 3,
                    true, true, true, true, true,
                    Price::Luxury,
                    Distance::Close, Distance::Close, Distance::Close),
            ]);
        $bookingsProviderMock->method('hasEndBeenReached')
            ->will($this->onConsecutiveCalls(false, true, false, true, false, true));

        $map = [
            ['kprototype', ['gamma' => 1,
                'serviceStopFile' => '',
                'outputInterval' => '',
                'serviceOutput' => '']],
        ];
        $configMock = $this->createMock(ConfigProvider::class);
        $configMock->method('get')->will($this->returnValueMap($map));

        // Random mock is used to set the randomly selected center points.
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('generate')->will($this->onConsecutiveCalls(0,2));

        $distance = new DistanceMeasurement($configMock);
        $sut = new KPrototypeAlgorithm($bookingsProviderMock, $configMock, $distance, $randomMock);

        $clusters = $sut->run();
        $this->assertEquals(2, count($clusters));

        $this->assertEquals(1, $clusters[0]->getCenter()->getId());
        $this->assertEquals(1, count($clusters[0]->getAssociates()));
        $this->assertEquals(2, $clusters[0]->getAssociates()[0]->getBooking()->getId());

        $this->assertEquals(3, $clusters[1]->getCenter()->getId());
        $this->assertEquals(1, count($clusters[1]->getAssociates()));
        $this->assertEquals(4, $clusters[1]->getAssociates()[0]->getBooking()->getId());
    }
}
