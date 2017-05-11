<?php
use \PHPUnit\Framework\TestCase,
    org\bovigo\vfs\vfsStream;

require_once dirname(__DIR__) . "/Interfaces/DataIterator.php";
require_once dirname(__DIR__) . "/Utilities/Iterators/LoadIncrementalCsvDataIterator.php";

class LoadIncrementalCsvDataIteratorTest extends TestCase
{
    private $dataFile;
    private $countFile;
    private $configMock;

    protected function setUp()
    {
        $this->configMock = $this->createMock(ConfigProvider::class);

        // Creating mock data file with vfs (virtual file system).
        vfsStream::setup('home');
        $this->dataFile = vfsStream::url('home/test.csv');
        file_put_contents($this->dataFile, 'first;second;third
1;2;3
2;3;4');

        // Creating mock count file with vfs.
        $this->countFile = vfsStream::url('home/count.csv');
        file_put_contents($this->countFile, '2');
    }

    /**
     * @test
     */
    public function validFileShouldBeOpened() {
        new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile);
        $this->assertTrue(true);
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function invalidFileShouldThrowException() {
        new LoadIncrementalCsvDataIterator($this->configMock, "invalidFile");
    }

    /**
     * @test
     */
    public function gettingFirstLineShouldReturnCorrectData() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile);
        $line = $sut->current();
        $this->assertEquals(array('first' => '1','second' => '2','third' => '3'), $line);
    }

    /**
     * @test
     */
    public function gettingSecondLineShouldReturnCorrectData() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile);
        $sut->next();
        $line = $sut->current();
        $this->assertEquals(array('first' => '2','second' => '3','third' => '4'), $line);
    }

    /**
     * @test
     */
    public function gettingThirdLineShouldReturnFalse() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile);
        $sut->next();
        $sut->next();
        $line = $sut->current();
        $this->assertFalse($line);
    }

    /**
     * @test
     */
    public function validFieldOnFirstLineShouldReturnTrue() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile);
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    /**
     * @test
     */
    public function validFieldForSecondLineShouldReturnTrue() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile);
        $sut->next();
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    /**
     * @test
     */
    public function validFieldForThirdLineShouldReturnFalse() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile);
        $sut->next();
        $sut->next();
        $valid = $sut->valid();
        $this->assertFalse($valid);
    }

    /**
     * @test
     */
    public function afterARewindItShouldReturnFirstLine() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile);
        $sut->next();
        $sut->rewind();
        $line = $sut->current();
        $this->assertEquals(array('first' => '1','second' => '2','third' => '3'), $line);
    }

    /**
     * @test
     */
    public function afterARewindAndANextItShouldReturnTheSecondElement() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile);
        $sut->next();
        $sut->next();
        $sut->rewind();
        $sut->next();
        $line = $sut->current();
        $this->assertEquals(array('first' => '2','second' => '3','third' => '4'), $line);
    }

    /**
     * @test
     */
    public function providingDiffernentDeliminiterShouldReturnSingleString() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile, null,0, ',');
        $line = $sut->current();
        $this->assertEquals(array('first;second;third' => '1;2;3'), $line);
    }

    /**
     * @test
     */
    public function loopingThroughShouldReturnAllElements() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile);

        $result = [];
        foreach($sut as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertEquals(array(
            1 => array('first' => '1','second' => '2','third' => '3'),
            2 => array('first' => '2','second' => '3','third' => '4'),
        ), $result);
    }

    /**
     * @test
     */
    public function skipOneLineShouldReturnSecond() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile);
        $sut->skip(1);
        $line = $sut->current();
        $this->assertEquals(array('first' => '2','second' => '3','third' => '4'), $line);
    }

    /**
     * @test
     */
    public function skipTwoLineShouldReturnFalse() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile);
        $sut->skip(2);
        $line = $sut->current();
        $this->assertFalse($line);
    }

    /**
     * @test
     */
    public function gettingCountShouldReturn2() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->dataFile, $this->countFile);

        $bookingsCount = $sut->count();

        $this->assertEquals(2, $bookingsCount);
    }
}
