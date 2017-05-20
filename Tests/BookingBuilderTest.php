<?php
use \PHPUnit\Framework\TestCase;
require_once dirname(__DIR__) . "/Business/BookingBuilder.php";
require_once dirname(__DIR__) . "/Business/DataTypeClusterer.php";
require_once dirname(__DIR__) . "/Interfaces/Field.php";
require_once dirname(__DIR__) . "/Utilities/ConfigProvider.php";
require_once dirname(__DIR__) . "/Models/DataTypeCluster.php";
require_once dirname(__DIR__) . "/Models/Booking.php";
require_once dirname(__DIR__) . "/Models/IntegerField.php";
require_once dirname(__DIR__) . "/Models/BooleanField.php";
require_once dirname(__DIR__) . "/Models/FloatField.php";
require_once dirname(__DIR__) . "/Models/StringField.php";
require_once dirname(__DIR__) . "/Models/PriceField.php";
require_once dirname(__DIR__) . "/Models/DistanceField.php";
require_once dirname(__DIR__) . "/Models/Price.php";
require_once dirname(__DIR__) . "/Models/Distance.php";
require_once dirname(__DIR__) . '/Models/DayOfWeekField.php';
require_once dirname(__DIR__) . '/Models/MonthOfYearField.php';
require_once dirname(__DIR__) . '/Models/DayOfWeek.php';
require_once dirname(__DIR__) . '/Models/MonthOfYear.php';

class BookingBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function idAndBookingsFieldShouldBeCorrectlySettedOnTheBooking() {
        $id = 99;
        $integerField = new IntegerField('1', 1);
        $booleanField = new BooleanField('2', true);
        $floatField = new FloatField('3', 1.1, 1.1);
        $stringField = new StringField('4', '1');
        $priceField = new PriceField('5', Price::Budget);
        $distanceField = new DistanceField('6', Distance::Close);
        $dataTypeCluster = new DataTypeCluster(
            [$integerField],
            [$booleanField],
            [$floatField],
            [$stringField],
            [$priceField],
            [$distanceField],[],[]);

        $map = [
            ['idField', 'id'],
        ];
        $configMock = $this->createMock(ConfigProvider::class);
        $configMock->method('get')->will($this->returnValueMap($map));

        $dataTypeClustererMock = $this->createMock(DataTypeClusterer::class);
        $dataTypeClustererMock->method('get')->willReturn($dataTypeCluster);



        $sut = new BookingBuilder($configMock, $dataTypeClustererMock);

        $booking = $sut->fromRawData(['id' => $id]);

        $this->assertEquals($id, $booking->getId());
        $this->assertEquals($integerField, $booking->getFieldsByType(IntegerField::class)[0]);
        $this->assertEquals($booleanField, $booking->getFieldsByType(BooleanField::class)[0]);
        $this->assertEquals($floatField, $booking->getFieldsByType(FloatField::class)[0]);
        $this->assertEquals($stringField, $booking->getFieldsByType(StringField::class)[0]);
        $this->assertEquals($distanceField, $booking->getFieldsByType(DistanceField::class)[0]);
        $this->assertEquals($priceField, $booking->getFieldsByType(PriceField::class)[0]);
    }
}
