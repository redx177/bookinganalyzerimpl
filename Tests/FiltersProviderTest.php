<?php
require_once dirname(__DIR__) . "/Business/FiltersProvider.php";
require_once dirname(__DIR__) . "/Business/DataTypeClusterer.php";
require_once dirname(__DIR__) . "/Models/DataTypeCluster.php";
require_once dirname(__DIR__) . "/Models/Filters.php";

use PHPUnit\Framework\TestCase;

class FiltersProviderTest extends TestCase
{
    /**
     * @test
     */
    public function filtersDataShouldBeMappedToCorrectDataTypes() {
        $rawData = ['action' => 'actionValue'];

        $dataTypeClustererMock = $this->createMock(DataTypeClusterer::class);
        $dataTypeClustererMock->method('get')
            ->will($this->returnCallback(function() {
                return new DataTypeCluster(['a' => 'a'], ['b' => 'b'], ['c' => 'c'],
                    ['d' => 'd'], ['e' => 'e'], ['f' => 'f']);
            }));

        $sut = new FiltersProvider($dataTypeClustererMock);

        $filters = $sut->get($rawData);
        $this->assertEquals('actionValue', $filters->getAction());
        $this->assertEquals(['a' => 'a'], $filters->getIntegerFields());
        $this->assertEquals(['b' => 'b'], $filters->getBooleanFields());
        $this->assertEquals(['c' => 'c'], $filters->getFloatFields());
        $this->assertEquals(['d' => 'd'], $filters->getStringFields());
        $this->assertEquals(['e' => 'e'], $filters->getPriceFields());
        $this->assertEquals(['f' => 'f'], $filters->getDistanceFields());
    }
}
