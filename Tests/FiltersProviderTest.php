<?php
require_once dirname(__DIR__) . '/Interfaces/Field.php';
require_once dirname(__DIR__) . "/Business/FiltersProvider.php";
require_once dirname(__DIR__) . "/Business/DataTypeClusterer.php";
require_once dirname(__DIR__) . "/Models/DataTypeCluster.php";
require_once dirname(__DIR__) . "/Models/Filters.php";
require_once dirname(__DIR__) . "/Models/Filter.php";
require_once dirname(__DIR__) . '/Models/IntegerField.php';
require_once dirname(__DIR__) . '/Models/BooleanField.php';
require_once dirname(__DIR__) . '/Models/FloatField.php';
require_once dirname(__DIR__) . '/Models/StringField.php';
require_once dirname(__DIR__) . '/Models/DistanceField.php';
require_once dirname(__DIR__) . '/Models/PriceField.php';
require_once dirname(__DIR__) . '/Models/Price.php';
require_once dirname(__DIR__) . '/Models/Distance.php';
require_once dirname(__DIR__) . '/Models/DayOfWeekField.php';
require_once dirname(__DIR__) . '/Models/MonthOfYearField.php';
require_once dirname(__DIR__) . '/Models/DayOfWeek.php';
require_once dirname(__DIR__) . '/Models/MonthOfYear.php';

use \PHPUnit\Framework\TestCase,
    org\bovigo\vfs\vfsStream;

class FiltersProviderTest extends TestCase
{
    private $dataTypeClustererMock;

    protected function setUp()
    {
        $this->dataTypeClustererMock = $this->createMock(DataTypeClusterer::class);
        $this->dataTypeClustererMock->method('get')
            ->will($this->returnCallback(function () {
                return new DataTypeCluster(
                        ['a1' => new IntegerField('a1', '1'), 'a2' => new IntegerField('a2', ['2', '3'])],
                        ['b' => new BooleanField('b', true)],
                        ['c' => new FloatField('c', 2.2, 2.2)],
                        ['d' => new StringField('d', 'd')],
                        ['e' => new PriceField('e', Price::Budget)],
                        ['f' => new DistanceField('f', Distance::Close)],[],[]);
            }));
    }

    /**
     * @test
     */
    public function filtersDataShouldBeMappedToCorrectDataTypes() {
        $rawData = ['action' => 'actionValue'];

        $sut = new FiltersProvider($this->dataTypeClustererMock, '', '');

        $filters = $sut->get($rawData);
        $filterSet = $filters->getFilters();
        $this->assertEquals('actionValue', $filters->getAction());
        $this->assertEquals(7, count($filterSet));
        $this->assertEquals('a1', $filterSet[0]->getName());
        $this->assertEquals('1', $filterSet[0]->getValue());
        $this->assertEquals('int', $filterSet[0]->getType());
        $this->assertEquals('a2', $filterSet[1]->getName());
        $this->assertEquals('2', $filterSet[1]->getValue()[0]);
        $this->assertEquals('3', $filterSet[1]->getValue()[1]);
        $this->assertEquals('int', $filterSet[1]->getType());
        $this->assertEquals('b', $filterSet[2]->getName());
        $this->assertEquals(true, $filterSet[2]->getValue());
        $this->assertEquals('bool', $filterSet[2]->getType());
        $this->assertEquals('c', $filterSet[3]->getName());
        $this->assertEquals(2.2, $filterSet[3]->getValue());
        $this->assertEquals('float', $filterSet[3]->getType());
        $this->assertEquals('d', $filterSet[4]->getName());
        $this->assertEquals('d', $filterSet[4]->getValue());
        $this->assertEquals('string', $filterSet[4]->getType());
        $this->assertEquals('e', $filterSet[5]->getName());
        $this->assertEquals(Price::Budget, $filterSet[5]->getValue());
        $this->assertEquals('Price', $filterSet[5]->getType());
        $this->assertEquals('f', $filterSet[6]->getName());
        $this->assertEquals(Distance::Close, $filterSet[6]->getValue());
        $this->assertEquals('Distance', $filterSet[6]->getType());
    }

    /**
     * @test
     */
    public function destinationShouldBeInTheCorrectFormat() {
        // Creating mock data file with vfs (virtual file system).
        vfsStream::setup('home');
        $testfile = vfsStream::url('home/test.csv');
        file_put_contents($testfile, '1;2;3
2;3;4
3;4;5');

        $sut = new FiltersProvider($this->dataTypeClustererMock, $testfile, '');
        $destinations = $sut->getDestinations();

        $this->assertEquals(3, count($destinations));

        $this->assertEquals(3, count($destinations[0]));
        $this->assertEquals(1, $destinations[0][0]);
        $this->assertEquals(2, $destinations[0][1]);
        $this->assertEquals(3, $destinations[0][2]);

        $this->assertEquals(3, count($destinations[1]));
        $this->assertEquals(2, $destinations[1][0]);
        $this->assertEquals(3, $destinations[1][1]);
        $this->assertEquals(4, $destinations[1][2]);

        $this->assertEquals(3, count($destinations[2]));
        $this->assertEquals(3, $destinations[2][0]);
        $this->assertEquals(4, $destinations[2][1]);
        $this->assertEquals(5, $destinations[2][2]);
    }

    /**
     * @test
     */
    public function invalidDestinationFileShouldReturnEmptyArrayWhenGettingDestinations() {
        $sut = new FiltersProvider($this->dataTypeClustererMock, 'invalid file', '');
        $destinations = $sut->getDestinations();

        $this->assertEquals([], $destinations);

    }
}
