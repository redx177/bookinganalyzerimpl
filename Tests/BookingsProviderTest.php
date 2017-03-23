<?php
use \PHPUnit\Framework\TestCase;
require_once dirname(__DIR__) . "/Business/BookingsProvider.php";
require_once dirname(__DIR__) . "/Business/DataTypeClusterer.php";
require_once dirname(__DIR__) . "/Models/Booking.php";
require_once dirname(__DIR__) . "/Models/DataTypeCluster.php";
require_once dirname(__DIR__) . "/Models/Distance.php";
require_once dirname(__DIR__) . "/Models/Price.php";
require_once dirname(__DIR__) . "/Models/Filters.php";
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
        ['idField' => '33']];

    protected function setUp()
    {
        $this->csvIteratorMock = CsvIteratorMock::get($this, $this->mockData);
        
        $this->dataTypeClustererMock = $this->createMock(DataTypeClusterer::class);
        $this->dataTypeClustererMock->method('get')
            ->will($this->returnCallback(function($rawData) {
                return $rawData['idField'] == '31'
                    // For first item...
                    ? new DataTypeCluster(['int' => 'a'], ['bool' => 'b'], ['float' => 'c'], ['str' => 'd'], ['pri' => 'e'], ['dist' => 'f'])
                    // For other items...
                    : new DataTypeCluster(['int' => 'a1'], ['bool' => 'b1'], ['float' => 'c1'], ['str' => 'd1'], ['pri' => 'e1'], ['dist' => 'f1']);
            }));
        
        $this->configMock = $this->createMock(ConfigProvider::class);
        $this->configMock->method('get')
            ->willReturn('idField');
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
        $this->csvIteratorMock
            ->expects($this->once())
            ->method('skip')
            ->with($this->equalTo(1));

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(1,2);

        $this->assertEquals(2, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(33, $data[1]->getId());
    }

    /**
     * @test
     */
    public function getItemCountShouldReturn5() {
        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);

        $this->assertEquals(3, $sut->getItemCount());
    }

    /**
     * @test
     */
    public function bookingDataShouldBeMappedToCorrectDataTypes() {
        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0,1);

        $this->assertEquals(31, $data[0]->getId());
        $this->assertEquals(['int' => 'a'], $data[0]->getIntegerFields());
        $this->assertEquals(['bool' => 'b'], $data[0]->getBooleanFields());
        $this->assertEquals(['float' => 'c'], $data[0]->getFloatFields());
        $this->assertEquals(['str' => 'd'], $data[0]->getStringFields());
        $this->assertEquals(['pri' => 'e'], $data[0]->getPriceFields());
        $this->assertEquals(['dist' => 'f'], $data[0]->getDistanceFields());
    }

    /**
     * @test
     */
    public function filteringIntegerValueShouldRemoveNonMatchingItems() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getIntegerFields')
            ->willReturn(['int' => 'a1']);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(2, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(33, $data[1]->getId());
    }

    /**
     * @test
     */
    public function filteringBooleanValueShouldRemoveNonMatchingItems() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getBooleanFields')
            ->willReturn(['bool' => 'b1']);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(2, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(33, $data[1]->getId());
    }

    /**
     * @test
     */
    public function filteringFloatValueShouldRemoveNonMatchingItems() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getFloatFields')
            ->willReturn(['float' => 'c1']);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(2, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(33, $data[1]->getId());
    }

    /**
     * @test
     */
    public function filteringStringValueShouldRemoveNonMatchingItems() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getStringFields')
            ->willReturn(['str' => 'd1']);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(2, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(33, $data[1]->getId());
    }

    /**
     * @test
     */
    public function filteringPriceValueShouldRemoveNonMatchingItems() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getPriceFields')
            ->willReturn(['pri' => 'e1']);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(2, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(33, $data[1]->getId());
    }

    /**
     * @test
     */
    public function filteringDistanceValueShouldRemoveNonMatchingItems() {
        $filtersMock = $this->createMock(Filters::class);
        $filtersMock->method('getDistanceFields')
            ->willReturn(['dist' => 'f1']);

        $sut = new BookingsProvider($this->csvIteratorMock, $this->dataTypeClustererMock, $this->configMock);
        $data = $sut->getSubset(0, 3, $filtersMock);

        $this->assertEquals(2, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(33, $data[1]->getId());
    }
}
