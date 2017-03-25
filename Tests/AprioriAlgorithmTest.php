<?php
require_once dirname(__DIR__) . '/Business/AprioriAlgorithm.php';
require_once dirname(__DIR__) . '/Business/BookingsProvider.php';
require_once dirname(__DIR__) . '/Models/Booking.php';
require_once dirname(__DIR__) . '/Models/Histogram.php';

use PHPUnit\Framework\TestCase;

class AprioriAlgorithmTest extends TestCase
{
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

    /**
     * @test
     */
    public function singleIntRoomsWithNoFilter() {
        $bookingsProviderMock = $this->createMock(BookingsProvider::class);
        $bookingsProviderMock->method('getSubset')
            ->will($this->onConsecutiveCalls([
                $this->GetBooking(
                    3, 1, 1,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    3, 2, 2,
                    true, true, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Close, Distance::Empty, Distance::Empty),
            ],[
                $this->GetBooking(
                    3, 3, 3,
                    false, false, true, true, true,
                    40.45538, -3.79278,
                    Price::Budget,
                    Distance::Empty, Distance::Close, Distance::Close),
            ]));

        $sut = new AprioriAlgorithm($bookingsProviderMock);
        $sut->run();
    }
}
