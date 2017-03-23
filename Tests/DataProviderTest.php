<?php
use \PHPUnit\Framework\TestCase;
require_once dirname(__DIR__) . "/Business/DataProvider.php";
require_once dirname(__DIR__) . "/Business/DataTypeClusterer.php";
require_once dirname(__DIR__) . "/Models/Booking.php";
require_once dirname(__DIR__) . "/Models/DataTypeCluster.php";
require_once dirname(__DIR__) . "/Models/Distance.php";
require_once dirname(__DIR__) . "/Models/Price.php";
require_once dirname(__DIR__) . "/Utilities/CsvIterator.php";
require_once dirname(__DIR__) . "/Utilities/ConfigProvider.php";
require_once __DIR__ . "/CsvIteratorMock.php";

class DataProviderTest extends TestCase
{
    private $csvIteratorMock;
    private $dataTypeClustererMock;

    private $mockData = [
        ['id' => '31','intA' => '1', 'intB' => '2', 'intC' => '3'],
        ['id' => '32','intA' => '2', 'intB' => '3', 'intC' => '4'],
        ['id' => '33','intA' => '3', 'intB' => '4', 'intC' => '5']];

    protected function setUp()
    {
        $this->csvIteratorMock = CsvIteratorMock::get($this, $this->mockData);
        $this->dataTypeClustererMock = $this->createMock(DataTypeClusterer::class);
        $this->dataTypeClustererMock->method('get')
            ->will($this->returnCallback(function($rawData) { return new DataTypeCluster($rawData['id'], [], [], [], [], [], []); }));
    }

    /**
     * @test
     */
    public function from0AndCount2ShouldReturnTheFirst2Items() {
        $sut = new DataProvider($this->csvIteratorMock, $this->dataTypeClustererMock);
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

        $sut = new DataProvider($this->csvIteratorMock, $this->dataTypeClustererMock);
        $data = $sut->getSubset(1,2);

        $this->assertEquals(2, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(33, $data[1]->getId());
    }

    /**
     * @test
     */
    public function getItemCountShouldReturn5() {
        $sut = new DataProvider($this->csvIteratorMock, $this->dataTypeClustererMock);

        $this->assertEquals(3, $sut->getItemCount());
    }
}
