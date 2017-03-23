<?php
use \PHPUnit\Framework\TestCase;
require_once dirname(__DIR__) . "/Business/DataProvider.php";
require_once dirname(__DIR__) . "/Models/Booking.php";
require_once dirname(__DIR__) . "/Models/Distance.php";
require_once dirname(__DIR__) . "/Models/Price.php";
require_once dirname(__DIR__) . "/Utilities/CsvIterator.php";
require_once dirname(__DIR__) . "/Utilities/ConfigProvider.php";
require_once __DIR__ . "/CsvIteratorMock.php";

class DataProviderTest extends TestCase
{
    private $csvIteratorMock;
    private $configMock;

    private $mockData = [
        ['id' => '31','intA' => '1', 'intB' => '2', 'intC' => '3'],
        ['id' => '32','intA' => '2', 'intB' => '3', 'intC' => '4'],
        ['id' => '33','intA' => '3', 'intB' => '4', 'intC' => '5'],
        ['id' => '34','floatA' => '33.22', 'strB' => 'CH12.12.12', 'boolC' => '1.0'],
        ['id' => '35','intA' => '5', 'boolB' => '0.0', 'strC' => '12.12.12'],
        ['id' => '36','distA' => '', 'distB' => 'close', 'priC' => 'budget'],
        ['id' => '37','priA' => '', 'priB' => 'luxury', 'priC' => 'budget']];

    protected function setUp()
    {
        $this->csvIteratorMock = CsvIteratorMock::get($this, $this->mockData);

        $this->configMock = $this->createMock(ConfigProvider::class);

        $parameterMap = [
            ['integerFields',['intA','intB','intC']],
            ['booleanFields',['boolA','boolB','boolC']],
            ['floatFields',['floatA','floatB','floatC']],
            ['stringFields',['strA','strB','strC']],
            ['distanceFields',['distA','distB','distC']],
            ['priceFields',['priA','priB','priC']],
            ['idField','id']
        ];
        $this->configMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($parameterMap));
    }

    /**
     * @test
     */
    public function from0AndCount2ShouldReturnTheFirst2Items() {
        $sut = new DataProvider($this->csvIteratorMock, $this->configMock);
        $data = $sut->getSubset(0,2);

        $this->assertEquals(2, count($data));
        $this->assertEquals(31, $data[0]->getId());
        $this->assertEquals(['intA' => 1, 'intB' => 2, 'intC' => 3], $data[0]->getIntegerFields());
        $this->assertEquals(32, $data[1]->getId());
        $this->assertEquals(['intA' => 2, 'intB' => 3, 'intC' => 4], $data[1]->getIntegerFields());
    }

    /**
     * @test
     */
    public function from1AndCount2ShouldReturn2ItemsAndSkipping1() {
        $this->csvIteratorMock
            ->expects($this->once())
            ->method('skip')
            ->with($this->equalTo(1));

        $sut = new DataProvider($this->csvIteratorMock, $this->configMock);
        $data = $sut->getSubset(1,2);

        $this->assertEquals(2, count($data));
        $this->assertEquals(32, $data[0]->getId());
        $this->assertEquals(['intA' => 2, 'intB' => 3, 'intC' => 4], $data[0]->getIntegerFields());
        $this->assertEquals(33, $data[1]->getId());
        $this->assertEquals(['intA' => 3, 'intB' => 4, 'intC' => 5], $data[1]->getIntegerFields());
    }

    /**
     * @test
     */
    public function getItemCountShouldReturn5() {
        $sut = new DataProvider($this->csvIteratorMock, $this->configMock);

        $this->assertEquals(7, $sut->getItemCount());
    }

    /**
     * @test
     */
    public function fieldShouldBeInTheCorrespondingTypedFields() {
        $sut = new DataProvider($this->csvIteratorMock, $this->configMock);

        $data = $sut->getSubset(3,4);
        $this->assertEquals(4, count($data));

        $this->assertEquals(34, $data[0]->getId());
        $this->assertEquals(['floatA' => 33.22], $data[0]->getFloatFields());
        $this->assertEquals(['strB' => 'CH12.12.12'], $data[0]->getStringFields());
        $this->assertEquals(['boolC' => true], $data[0]->getBooleanFields());

        $this->assertEquals(35, $data[1]->getId());
        $this->assertEquals(['intA' => 5], $data[1]->getIntegerFields());
        $this->assertEquals(['boolB' => false], $data[1]->getBooleanFields());
        $this->assertEquals(['strC' => '12.12.12'], $data[1]->getStringFields());

        $this->assertEquals(36, $data[2]->getId());
        $this->assertEquals(['distA' => Distance::Empty,'distB' => Distance::Close], $data[2]->getDistanceFields());
        $this->assertEquals(['priC' => Price::Budget], $data[2]->getPriceFields());

        $this->assertEquals(37, $data[3]->getId());
        $this->assertEquals(['priA' => Price::Empty,'priB' => Price::Luxury,'priC' => Price::Budget], $data[3]->getPriceFields());
    }
}
