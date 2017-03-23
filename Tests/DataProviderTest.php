<?php
use \PHPUnit\Framework\TestCase;
require_once dirname(__DIR__) . "/Business/DataProvider.php";
require_once dirname(__DIR__) . "/Utilities/CsvIterator.php";
require_once __DIR__ . "/CsvIteratorMock.php";

class DataProviderTest extends TestCase
{
    private $csvIteratorMock;

    private $mockData = [
        [1, 2, 3],
        [2, 3, 4],
        [3, 4, 5],
        ['33.22', 'CH12.12.12', 'blabla'],
        ['5', 6, '12.12.12']];

    protected function setUp()
    {
        $this->csvIteratorMock = CsvIteratorMock::get($this, $this->mockData);
    }

    /**
     * @test
     */
    public function from0AndCount2ShouldReturnTheFirst2Items() {
        $sut = new DataProvider($this->csvIteratorMock);
        $data = $sut->getSubset(0,2);

        $this->assertEquals(2, count($data));
        $this->assertEquals([1,2,3], $data[0]);
        $this->assertEquals([2,3,4], $data[1]);
    }

    /**
     * @test
     */
    public function from1AndCount2ShouldReturn2ItemsAndSkipping1() {
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

    /**
     * @test
     */
    public function getItemCountShouldReturn5() {
        $sut = new DataProvider($this->csvIteratorMock);

        $this->assertEquals(5, $sut->getItemCount());
    }

    /**
     * @test
     */
    public function dataShouldBeTyped() {
        $sut = new DataProvider($this->csvIteratorMock);
        $data = $sut->getSubset(3,2);

        $this->assertEquals([33.22, 'CH12.12.12', 'blabla'], $data[0]);
        $this->assertEquals([5,6,'12.12.12'], $data[1]);
    }
}
