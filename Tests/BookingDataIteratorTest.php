<?php
use \PHPUnit\Framework\TestCase;
require_once dirname(__DIR__) . "/Interfaces/DataIterator.php";
require_once dirname(__DIR__) . "/Interfaces/Field.php";
require_once dirname(__DIR__) . "/Business/BookingDataIterator.php";
require_once dirname(__DIR__) . "/Business/BookingBuilder.php";
require_once dirname(__DIR__) . "/Models/Booking.php";
require_once dirname(__DIR__) . "/Models/DataTypeCluster.php";
require_once dirname(__DIR__) . "/Models/IntegerField.php";
require_once dirname(__DIR__) . "/Models/BooleanField.php";
require_once dirname(__DIR__) . "/Models/FloatField.php";
require_once dirname(__DIR__) . "/Models/StringField.php";
require_once dirname(__DIR__) . "/Models/PriceField.php";
require_once dirname(__DIR__) . "/Models/DistanceField.php";
require_once dirname(__DIR__) . "/Models/Price.php";
require_once dirname(__DIR__) . "/Models/Distance.php";

class BookingDataIteratorTest extends TestCase
{
    /**
     * @test
     */
    public function idAndBookingsFieldShouldBeCorrectlySettedOnTheBooking() {
        $expectedCount = 99;
        $expectedKey = 199;
        $expectedValid = true;
        $expectedCurrent = new Booking(1, new DataTypeCluster([],[],[],[],[],[]));

        $dataIteratorMock = $this->createMock(DataIterator::class);
        $dataIteratorMock
            ->expects($this->exactly(1))
            ->method('skip');
        $dataIteratorMock
            ->expects($this->exactly(1))
            ->method('count')
            ->will($this->returnValue($expectedCount));
        $dataIteratorMock
            ->expects($this->exactly(1))
            ->method('current');
        $dataIteratorMock
            ->expects($this->exactly(1))
            ->method('next');
        $dataIteratorMock
            ->expects($this->exactly(1))
            ->method('key')
            ->will($this->returnValue($expectedKey));
        $dataIteratorMock
            ->expects($this->exactly(1))
            ->method('valid')
            ->will($this->returnValue($expectedValid));
        $dataIteratorMock
            ->expects($this->exactly(1))
            ->method('rewind');

        $bookingBuilderMock = $this->createMock(BookingBuilder::class);
        $bookingBuilderMock
            ->expects($this->exactly(1))
            ->method('fromRawData')
            ->will($this->returnValue($expectedCurrent));

        $sut = new BookingDataIterator($dataIteratorMock, $bookingBuilderMock);

        // Methods with no return value.
        $sut->skip(5);
        $sut->next();
        $sut->rewind();

        // Methods with return value.
        $actualCount = $sut->count();
        $actualKey = $sut->key();
        $actualValid = $sut->valid();
        $actualCurrent = $sut->current();

        $this->assertEquals($expectedCount, $actualCount);
        $this->assertEquals($expectedKey, $actualKey);
        $this->assertEquals($expectedValid, $actualValid);
        $this->assertEquals($expectedCurrent, $actualCurrent);


    }
}
