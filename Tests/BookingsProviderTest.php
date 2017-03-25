<?php
use \PHPUnit\Framework\TestCase;
require_once dirname(__DIR__) . "/Business/BookingsProvider.php";
require_once dirname(__DIR__) . "/Business/DataTypeClusterer.php";
require_once dirname(__DIR__) . "/Models/Booking.php";
require_once dirname(__DIR__) . "/Models/DataTypeCluster.php";
require_once dirname(__DIR__) . "/Models/Distance.php";
require_once dirname(__DIR__) . "/Models/Price.php";
require_once dirname(__DIR__) . "/Models/Filters.php";
require_once dirname(__DIR__) . "/Models/Filter.php";
require_once dirname(__DIR__) . "/Models/Field.php";
require_once dirname(__DIR__) . "/Models/IntegerField.php";
require_once dirname(__DIR__) . "/Models/BooleanField.php";
require_once dirname(__DIR__) . "/Models/FloatField.php";
require_once dirname(__DIR__) . "/Models/StringField.php";
require_once dirname(__DIR__) . "/Models/PriceField.php";
require_once dirname(__DIR__) . "/Models/DistanceField.php";
require_once dirname(__DIR__) . "/Utilities/CsvIterator.php";
require_once dirname(__DIR__) . "/Utilities/ConfigProvider.php";
require_once __DIR__ . "/CsvIteratorMock.php";

class BookingsProviderTest extends TestCase
{
    private $csvIteratorMock;
    private $dataTypeClustererMock;
    private $configMock;

    private $mockData = [
        ['idField' => '31'],
        ['idField' => '32'],
        ['idField' => '33'],
        ['idField' => '34'],
        ['idField' => '35'],
        ['idField' => '36'],
        ['idField' => '37'],
        ['idField' => '38'],
        ['idField' => '39'],
        ['idField' => '40']];

    protected function setUp()
    {
        $this->csvIteratorMock = CsvIteratorMock::get($this, $this->mockData);
        
        $this->dataTypeClustererMock = $this->createMock(DataTypeClusterer::class);
        $this->dataTypeClustererMock->method('get')
            ->will($this->returnCallback(function($rawData) {
                if ($rawData['idField'] % 3 == 1)
                    return new DataTypeCluster(['int' => 4,'int2' => 5], ['bool' => false], ['float' => 2.20], ['str' => 'd'], ['pri' => Price::Empty], ['dist' => Distance::Empty]);
                if ($rawData['idField'] % 3 == 2)
                    return new DataTypeCluster(['int' => 15,'int2' => 17], ['bool' => true], ['float' => 2.21], ['str' => 'd1'], ['pri' => Price::Luxury], ['dist' => Distance::Close]);
                return new DataTypeCluster(['int' => 20,'int2' => 21], ['bool' => false], ['float' => 2.22], ['str' => 'd2'], ['pri' => Price::Budget], ['dist' => Distance::Empty]);
            }));

        $map = array(
            array('idField', 'idField'),
            array('atLeastFilterFields', ['int'])
        );
        $this->configMock = $this->createMock(ConfigProvider::class);
        $this->configMock->method('get')
            ->will($this->returnValueMap($map));
    }

    /**
     * @test
     */
    public function from0AndCount2ShouldReturnTheFirst2Items() {
        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0,2);

        $this->assertEquals(2, count($data));
        $this->assertEquals(31, $data[0]->getId());
        $this->assertEquals(32, $data[1]->getId());
    }

    /**
     * @test
     */
    public function from1AndCount2ShouldReturn2ItemsAndSkipping1() {
        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(1,2);

        $this->assertEquals(2, count($data));
        $this->assertEquals(32, $data[1]->getId());
        $this->assertEquals(33, $data[2]->getId());
    }

    /**
     * @test
     */
    public function from2AndCount2ShouldReturn1ItemsAndSkipping2() {
        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(9,2);

        $this->assertEquals(1, count($data));
        $this->assertEquals(40, $data[9]->getId());
    }

    /**
     * @test
     */
    public function from99999999AndCount2ShouldReturn1ItemsAndSkipping2() {
        $this->csvIteratorMock
            ->expects($this->exactly(count($this->mockData)+1))
            ->method('next');
        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(99999999,2);

        $this->assertEquals(1, count($data));
        $this->assertEquals(40, $data[9]->getId());
    }

    /**
     * @test
     */
    public function from0AndCount99999999ShouldReturn1ItemsAndSkipping2() {
        $this->csvIteratorMock
            ->expects($this->exactly(count($this->mockData)+1))
            ->method('next');
        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0,99999999);

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
        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0,1);

        $this->assertEquals(31, $data[0]->getId());
        $this->assertEquals('int', $data[0]->getFieldsByType(int::class)[0]->getName());
        $this->assertEquals(4, $data[0]->getFieldsByType(int::class)[0]->getValue());
        $this->assertEquals('int2', $data[0]->getFieldsByType(int::class)[1]->getName());
        $this->assertEquals(5, $data[0]->getFieldsByType(int::class)[1]->getValue());
        $this->assertEquals('bool', $data[0]->getFieldsByType(bool::class)[0]->getName());
        $this->assertEquals(false, $data[0]->getFieldsByType(bool::class)[0]->getValue());
        $this->assertEquals('float', $data[0]->getFieldsByType(float::class)[0]->getName());
        $this->assertEquals(2.2, $data[0]->getFieldsByType(float::class)[0]->getValue());
        $this->assertEquals('str', $data[0]->getFieldsByType(string::class)[0]->getName());
        $this->assertEquals('d', $data[0]->getFieldsByType(string::class)[0]->getValue());
        $this->assertEquals('pri', $data[0]->getFieldsByType(Price::class)[0]->getName());
        $this->assertEquals(Price::Empty, $data[0]->getFieldsByType(Price::class)[0]->getValue());
        $this->assertEquals('dist', $data[0]->getFieldsByType(Distance::class)[0]->getName());
        $this->assertEquals(Distance::Empty, $data[0]->getFieldsByType(Distance::class)[0]->getValue());
    }

    /**
     * @test
     */
    public function filteringIntegerValueShouldRemoveNonMatchingItems() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getFilters')
            ->willReturn([new Filter('int', 20, int::class)]);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(3, count($data));
        $this->assertEquals(33, $data[0]->getId());
        $this->assertEquals(36, $data[1]->getId());
        $this->assertEquals(39, $data[2]->getId());
    }

    /**
     * @test
     */
    public function filteringIntegerValueWithAtLeastConditionShouldRemoveNonMatchingItems() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getFilters')
            ->willReturn([new Filter('int', 5, int::class)]);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(3, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(33, $data[1]->getId());
        $this->assertEquals(35, $data[2]->getId());
    }

    /**
     * @test
     */
    public function filteringIntegerWithMultiSelectionShouldRemoveNonMatchingItems() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getFilters')
            ->willReturn([new Filter('int2', [5,21], int::class)]);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(3, count($data));
        $this->assertEquals(31, $data[0]->getId());
        $this->assertEquals(33, $data[1]->getId());
        $this->assertEquals(34, $data[2]->getId());
    }

    /**
     * @test
     */
    public function filteringBooleanValueShouldRemoveNonMatchingItems() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getFilters')
            ->willReturn([new Filter('bool', true, bool::class)]);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(3, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(35, $data[1]->getId());
        $this->assertEquals(38, $data[2]->getId());
    }

    /**
     * @test
     */
    public function filteringFloatValueShouldRemoveNonMatchingItems() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getFilters')
            ->willReturn([new Filter('float', 2.21, float::class)]);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(3, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(35, $data[1]->getId());
        $this->assertEquals(38, $data[2]->getId());
    }

    /**
     * @test
     */
    public function filteringStringValueShouldRemoveNonMatchingItems() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getFilters')
            ->willReturn([new Filter('str', 'd1', string::class)]);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(3, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(35, $data[1]->getId());
        $this->assertEquals(38, $data[2]->getId());
    }

    /**
     * @test
     */
    public function filteringPriceValueShouldRemoveNonMatchingItems() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getFilters')
            ->willReturn([new Filter('pri', Price::Luxury, Price::class)]);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(3, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(35, $data[1]->getId());
        $this->assertEquals(38, $data[2]->getId());
    }

    /**
     * @test
     */
    public function filteringDistanceValueShouldRemoveNonMatchingItems()
    {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getFilters')
            ->willReturn([new Filter('dist', Distance::Close, Distance::class)]);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(3, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(35, $data[1]->getId());
        $this->assertEquals(38, $data[2]->getId());
    }

    /**
     * @test
     */
    public function whenOnPage2TheIndexesShouldStartAt3()
    {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getFilters')
            ->willReturn([new Filter('int', 10, int::class)]);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(3, 3, $filtersMock);

        $this->assertEquals(3, count($data));
        $this->assertEquals(36, $data[3]->getId());
        $this->assertEquals(38, $data[4]->getId());
        $this->assertEquals(39, $data[5]->getId());
    }

    /**
     * @test
     */
    public function whenGetting5OutOf10ItemsThenHasBeenReachedShouldReturnFalse() {
        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $sut->getSubset(0, 5);

        $this->assertFalse($sut->hasEndBeenReached());
    }

    /**
     * @test
     */
    public function whenGettingAllItemsThenHasBeenReachedShouldReturnTrue() {
        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $sut->getSubset(0, 10);

        $this->assertTrue($sut->hasEndBeenReached());
    }

    /**
     * @test
     */
    public function whenGettingMoreItemThanThereAreThenHasBeenReachedShouldReturnTrue() {
        $this->csvIteratorMock
            ->expects($this->exactly(count($this->mockData)+1))
            ->method('next');
        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $sut->getSubset(0, 9999999);

        $this->assertTrue($sut->hasEndBeenReached());
    }

    /**
     * @test
     */
    public function whenFilteringAndGetting5OutOf10ItemsThenHasBeenReachedShouldReturnFalse() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getFilters')
            ->willReturn([new Filter('int', 20, int::class)]);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $sut->getSubset(0, 2, $filtersMock);

        $this->assertFalse($sut->hasEndBeenReached());
    }

    /**
     * @test
     */
    public function whenFilteringAndGettingAllItemsThenHasBeenReachedShouldReturnTrue() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getFilters')
            ->willReturn([new Filter('int', 20, int::class)]);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertTrue($sut->hasEndBeenReached());
    }

    /**
     * @test
     */
    public function whenFilteringAndGettingMoreItemThanThereAreThenHasBeenReachedShouldReturnTrue() {
        $this->csvIteratorMock
            ->expects($this->exactly(count($this->mockData)+1))
            ->method('next');
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getFilters')
            ->willReturn([new Filter('int', 20, int::class)]);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $sut->getSubset(0, 9999999, $filtersMock);

        $this->assertTrue($sut->hasEndBeenReached());
    }

//    /**
//     * @test
//     */
//    public function filteringShouldAffectItemCount() {
//        $filtersMock = $this->createMock(Filters::class);
//        $filtersMock->method('getDistanceFields')
//            ->willReturn(['int' => 4]);
//
//        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
//        $itemCount = $sut->getItemCount();
//
//        $this->assertEquals(1, $itemCount);
//    }
}
