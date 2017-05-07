<?php
use \PHPUnit\Framework\TestCase;
require_once dirname(__DIR__) . "/Interfaces/Field.php";
require_once dirname(__DIR__) . "/Interfaces/DataIterator.php";
require_once dirname(__DIR__) . "/Business/BookingsProvider.php";
require_once dirname(__DIR__) . "/Business/DataTypeClusterer.php";
require_once dirname(__DIR__) . "/Business/BookingDataIterator.php";
require_once dirname(__DIR__) . "/Models/Booking.php";
require_once dirname(__DIR__) . "/Models/DataTypeCluster.php";
require_once dirname(__DIR__) . "/Models/Distance.php";
require_once dirname(__DIR__) . "/Models/Price.php";
require_once dirname(__DIR__) . "/Models/IntegerField.php";
require_once dirname(__DIR__) . "/Models/BooleanField.php";
require_once dirname(__DIR__) . "/Models/FloatField.php";
require_once dirname(__DIR__) . "/Models/StringField.php";
require_once dirname(__DIR__) . "/Models/PriceField.php";
require_once dirname(__DIR__) . "/Models/DistanceField.php";
require_once dirname(__DIR__) . "/Utilities/Iterators/LoadIncrementalCsvDataIterator.php";
require_once dirname(__DIR__) . "/Utilities/ConfigProvider.php";
require_once __DIR__ . "/BookingDataIteratorMock.php";

class BookingsProviderTest extends TestCase
{
    private $csvIteratorMock;

    private $ids = ['31', '32', '33', '34', '35', '36', '37', '38', '39', '40'];

    protected function setUp()
    {
        $mockData = [];
        foreach ($this->ids as $id) {
            $cluster = null;
            if ($id % 3 == 1)
                $cluster = new DataTypeCluster(
                    ['int'=>new IntegerField('int', 4), 'int2' => new IntegerField('int2', 5)],
                    ['bool' => new BooleanField('bool', false)],
                    ['float' => new FloatField('float', 2.20)],
                    ['str' => new StringField('str', 'd')],
                    ['pri' => new PriceField('pri', Price::Empty)],
                    ['dist' => new DistanceField('dist', Distance::Empty)]);
            elseif ($id % 3 == 2)
                $cluster =  new DataTypeCluster(
                    ['int' => new IntegerField('int', 15), 'int2' => new IntegerField('int2', 17)],
                    ['bool' => new BooleanField('bool', true)],
                    ['float' => new FloatField('float', 2.21)],
                    ['str' => new StringField('str', 'd1')],
                    ['pri' => new PriceField('pri', Price::Luxury)],
                    ['dist' => new DistanceField('dist', Distance::Close)]);
            else
                $cluster = new DataTypeCluster(
                    ['int' => new IntegerField('int', 20), 'int2' => new IntegerField('int2', 21)],
                    ['bool' => new BooleanField('bool', false)],
                    ['float' => new FloatField('float', 2.22)],
                    ['str' => new StringField('str', 'd2')],
                    ['pri' => new PriceField('pri', Price::Budget)],
                    ['dist' => new DistanceField('dist', Distance::Empty)]);
            $mockData[] = new Booking($id, $cluster);
        }
        $this->csvIteratorMock = BookingDataIteratorMock::get($this, $mockData);
    }

    /**
     * @test
     */
    public function from0AndCount2ShouldReturnTheFirst2Items() {
        $sut = new BookingsProvider($this->csvIteratorMock);
        $data = $sut->getSubset(2, 0);

        $this->assertEquals(2, count($data));
        $this->assertEquals(31, $data[0]->getId());
        $this->assertEquals(32, $data[1]->getId());
    }

    /**
     * @test
     */
    public function from1AndCount2ShouldReturn2ItemsAndSkipping1() {
        $sut = new BookingsProvider($this->csvIteratorMock);
        $data = $sut->getSubset(2, 1);

        $this->assertEquals(2, count($data));
        $this->assertEquals(32, $data[1]->getId());
        $this->assertEquals(33, $data[2]->getId());
    }

    /**
     * @test
     */
    public function from9AndCount2ShouldReturn1ItemsAndSkipping2() {
        $sut = new BookingsProvider($this->csvIteratorMock);
        $data = $sut->getSubset(2, 9);

        $this->assertEquals(1, count($data));
        $this->assertEquals(40, $data[9]->getId());
    }

    /**
     * @test
     */
    public function from99999999AndCount2ShouldReturn0Items() {
        $this->csvIteratorMock
            ->expects($this->exactly(count($this->ids)))
            ->method('next');
        $sut = new BookingsProvider($this->csvIteratorMock);
        $data = $sut->getSubset(2, 99999999);

        $this->assertEquals(0, count($data));
    }

    /**
     * @test
     */
    public function from0AndCount99999999ShouldReturn1ItemsAndSkipping0() {
        $this->csvIteratorMock
            ->expects($this->exactly(count($this->ids)))
            ->method('next');
        $sut = new BookingsProvider($this->csvIteratorMock);
        $data = $sut->getSubset(99999999, 0);

        $this->assertEquals(10, count($data));
        $this->assertEquals(31, $data[0]->getId());
        $this->assertEquals(32, $data[1]->getId());
        $this->assertEquals(33, $data[2]->getId());
        $this->assertEquals(34, $data[3]->getId());
        $this->assertEquals(35, $data[4]->getId());
        $this->assertEquals(36, $data[5]->getId());
        $this->assertEquals(37, $data[6]->getId());
        $this->assertEquals(38, $data[7]->getId());
        $this->assertEquals(39, $data[8]->getId());
        $this->assertEquals(40, $data[9]->getId());
    }

//    /**
//     * @test
//     */
//    public function getItemCountShouldReturn5() {
//        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
//
//        $this->assertEquals(3, $sut->getItemCount());
//    }

    /**
     * @test
     */
    public function bookingDataShouldBeMappedToCorrectDataTypes() {
        $sut = new BookingsProvider($this->csvIteratorMock);
        $data = $sut->getSubset(1,  0);

        $this->assertEquals(31, $data[0]->getId());
        $this->assertEquals('int', array_values($data[0]->getFieldsByType(IntegerField::class))[0]->getName());
        $this->assertEquals(4, array_values($data[0]->getFieldsByType(IntegerField::class))[0]->getValue());
        $this->assertEquals('int2', array_values($data[0]->getFieldsByType(IntegerField::class))[1]->getName());
        $this->assertEquals(5, array_values($data[0]->getFieldsByType(IntegerField::class))[1]->getValue());
        $this->assertEquals('bool', array_values($data[0]->getFieldsByType(BooleanField::class))[0]->getName());
        $this->assertEquals(false, array_values($data[0]->getFieldsByType(BooleanField::class))[0]->getValue());
        $this->assertEquals('float', array_values($data[0]->getFieldsByType(FloatField::class))[0]->getName());
        $this->assertEquals(2.2, array_values($data[0]->getFieldsByType(FloatField::class))[0]->getValue());
        $this->assertEquals('str', array_values($data[0]->getFieldsByType(StringField::class))[0]->getName());
        $this->assertEquals('d', array_values($data[0]->getFieldsByType(StringField::class))[0]->getValue());
        $this->assertEquals('pri', array_values($data[0]->getFieldsByType(PriceField::class))[0]->getName());
        $this->assertEquals(Price::Empty, array_values($data[0]->getFieldsByType(PriceField::class))[0]->getValue());
        $this->assertEquals('dist', array_values($data[0]->getFieldsByType(DistanceField::class))[0]->getName());
        $this->assertEquals(Distance::Empty, array_values($data[0]->getFieldsByType(DistanceField::class))[0]->getValue());
    }

    /**
     * @test
     */
    public function whenOnPage2TheIndexesShouldStartAt3()
    {
        $sut = new BookingsProvider($this->csvIteratorMock);
        $data = $sut->getSubset(3, 3);

        $this->assertEquals(3, count($data));
        $this->assertEquals(34, $data[3]->getId());
        $this->assertEquals(35, $data[4]->getId());
        $this->assertEquals(36, $data[5]->getId());
    }

    /**
     * @test
     */
    public function whenGetting5OutOf10ItemsThenHasBeenReachedShouldReturnFalse() {
        $sut = new BookingsProvider($this->csvIteratorMock);
        $sut->getSubset(5);

        $this->assertFalse($sut->hasEndBeenReached());
    }

    /**
     * @test
     */
    public function whenGettingAllItemsThenHasBeenReachedShouldReturnTrue() {
        $sut = new BookingsProvider($this->csvIteratorMock);
        $sut->getSubset(10);

        $this->assertTrue($sut->hasEndBeenReached());
    }

    /**
     * @test
     */
    public function whenGettingMoreItemThanThereAreThenHasBeenReachedShouldReturnTrue() {
        $this->csvIteratorMock
            ->expects($this->exactly(count($this->ids)))
            ->method('next');
        $sut = new BookingsProvider($this->csvIteratorMock);
        $sut->getSubset(9999999);

        $this->assertTrue($sut->hasEndBeenReached());
    }

    /**
     * @test
     */
    public function getLastPageShouldBeFullyPopulatedIfFromIsToHigh() {
        $this->csvIteratorMock
            ->expects($this->exactly(count($this->ids)))
            ->method('next');
        $sut = new BookingsProvider($this->csvIteratorMock);
        $sut->getSubset(2, 99999999);
        $data = $sut->getLastPageItems();

        $this->assertEquals(2, count($data));
        $this->assertEquals(39, $data[8]->getId());
        $this->assertEquals(40, $data[9]->getId());
    }

    /**
     * @test
     */
    public function getLastPageShouldBePartiallyPopulatedIfFromIsToHigh() {
        $this->csvIteratorMock
            ->expects($this->exactly(count($this->ids)))
            ->method('next');
        $sut = new BookingsProvider($this->csvIteratorMock);
        $sut->getSubset(3, 99999999);
        $data = $sut->getLastPageItems();

        $this->assertEquals(1, count($data));
        $this->assertEquals(40, $data[9]->getId());
    }

    /**
     * @test
     */
    public function callingGetSubsetASecondTimeShouldSkipTheFirstResults() {
        $sut = new BookingsProvider($this->csvIteratorMock);
        $sut->getSubset(2);
        $data = $sut->getSubset(2);

        $this->assertEquals(2, count($data));
        $this->assertEquals(33, $data[0]->getId());
        $this->assertEquals(34, $data[1]->getId());
    }
}
