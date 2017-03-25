<?php
require_once dirname(__DIR__) . "/Business/FiltersProvider.php";
require_once dirname(__DIR__) . "/Business/DataTypeClusterer.php";
require_once dirname(__DIR__) . "/Models/DataTypeCluster.php";
require_once dirname(__DIR__) . "/Models/Filters.php";
require_once dirname(__DIR__) . "/Models/Filter.php";

use PHPUnit\Framework\TestCase;

class FiltersProviderTest extends TestCase
{
    private $dataTypeClustererMock;

    protected function setUp()
    {
        $this->dataTypeClustererMock = $this->createMock(DataTypeClusterer::class);
        $this->dataTypeClustererMock->method('get')
            ->will($this->returnCallback(function () {
                return new DataTypeCluster(['a1' => 'a1a', 'a2' => ['a2a', 'a2b']], ['b' => 'b'], ['c' => 'c'],
                    ['d' => 'd'], ['e' => 'e'], ['f' => 'f']);
            }));
    }

    /**
     * @test
     */
    public function filtersDataShouldBeMappedToCorrectDataTypes() {
        $rawData = ['action' => 'actionValue'];

        $sut = new FiltersProvider($this->dataTypeClustererMock);

        $filters = $sut->get($rawData);
        $filterSet = $filters->getFilters();
        $this->assertEquals('actionValue', $filters->getAction());
        $this->assertEquals(7, count($filterSet));
        $this->assertEquals('a1', $filterSet[0]->getName());
        $this->assertEquals('a1a', $filterSet[0]->getValue());
        $this->assertEquals('int', $filterSet[0]->getType());
        $this->assertEquals('a2', $filterSet[1]->getName());
        $this->assertEquals('a2a', $filterSet[1]->getValue()[0]);
        $this->assertEquals('a2b', $filterSet[1]->getValue()[1]);
        $this->assertEquals('int', $filterSet[1]->getType());
        $this->assertEquals('b', $filterSet[2]->getName());
        $this->assertEquals('b', $filterSet[2]->getValue());
        $this->assertEquals('bool', $filterSet[2]->getType());
        $this->assertEquals('c', $filterSet[3]->getName());
        $this->assertEquals('c', $filterSet[3]->getValue());
        $this->assertEquals('float', $filterSet[3]->getType());
        $this->assertEquals('d', $filterSet[4]->getName());
        $this->assertEquals('d', $filterSet[4]->getValue());
        $this->assertEquals('string', $filterSet[4]->getType());
        $this->assertEquals('e', $filterSet[5]->getName());
        $this->assertEquals('e', $filterSet[5]->getValue());
        $this->assertEquals('Price', $filterSet[5]->getType());
        $this->assertEquals('f', $filterSet[6]->getName());
        $this->assertEquals('f', $filterSet[6]->getValue());
        $this->assertEquals('Distance', $filterSet[6]->getType());
    }
}
