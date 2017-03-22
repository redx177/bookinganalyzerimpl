<?php
use \PHPUnit\Framework\TestCase;
require_once dirname(__DIR__) . "/Business/DataProvider.php";
require_once dirname(__DIR__) . "/Utilities/CsvIterator.php";
require_once __DIR__ . "/CsvIteratorMock.php";

class DataProviderTest extends TestCase
{
    private $csvIteratorMock;

    protected function setUp()
    {
//        $this->csvIteratorMock = $this->createMock(CsvIterator::class);
//        $this->csvIteratorMock
//            ->method('current')
//            ->will($this->onConsecutiveCalls(
//                [1,2,3],
//                [2,3,4],
//                [3,4,5],
//                [4,5,6],
//                [5,6,7]
//            ));

        $this->csvIteratorMock = CsvIteratorMock::get($this, [
            [1,2,3],
            [2,3,4],
            [3,4,5],
            [4,5,6],
            [5,6,7]]);
    }

    public function testStartingFromTheBeginningReturnsTheCorrectElements() {
        $sut = new DataProvider($this->csvIteratorMock);
        $data = $sut->getSubset(0,2);

        $this->assertEquals(2, count($data));
        $this->assertEquals([1,2,3], $data[0]);
        $this->assertEquals([2,3,4], $data[1]);
    }

    public function testOffsetOfOneReturnsTheCorrectElements() {

        $this->csvIteratorMock
            ->expects($this->once())
            ->method('skip')
            ->with($this->equalTo(1));

        $sut = new DataProvider($this->csvIteratorMock);
        $data = $sut->getSubset(1,2);

        $this->assertEquals(2, count($data));
        $this->assertEquals([2,3,4], $data[0]);
        $this->assertEquals([3,4,5], $data[1]);
    }
}
