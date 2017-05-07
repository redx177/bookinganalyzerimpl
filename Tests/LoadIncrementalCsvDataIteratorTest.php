<?php
use \PHPUnit\Framework\TestCase,
    org\bovigo\vfs\vfsStream;

require_once dirname(__DIR__) . "/Interfaces/DataIterator.php";
require_once dirname(__DIR__) . "/Utilities/Iterators/LoadIncrementalCsvDataIterator.php";

class LoadIncrementalCsvDataIteratorTest extends TestCase
{
    private $testfile;
    private $configMock;

    protected function setUp()
    {
        $this->configMock = $this->createMock(ConfigProvider::class);

        // Creating mock data file with vfs (virtual file system).
        $this->testfile = 'home/test.csv';
        vfsStream::setup('home');
        $this->testfile = vfsStream::url($this->testfile);
        file_put_contents($this->testfile, 'first;second;third
1;2;3
2;3;4');
    }

    /**
     * @test
     */
    public function validFileShouldBeOpened() {
        new LoadIncrementalCsvDataIterator($this->configMock, $this->testfile);
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
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->testfile);
        $line = $sut->current();
        $this->assertEquals(array('first' => '1','second' => '2','third' => '3'), $line);
    }

    /**
     * @test
     */
    public function gettingSecondLineShouldReturnCorrectData() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->testfile);
        $sut->next();
        $line = $sut->current();
        $this->assertEquals(array('first' => '2','second' => '3','third' => '4'), $line);
    }

    /**
     * @test
     */
    public function gettingThirdLineShouldReturnFalse() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->testfile);
        $sut->next();
        $sut->next();
        $line = $sut->current();
        $this->assertFalse($line);
    }

    /**
     * @test
     */
    public function validFieldOnFirstLineShouldReturnTrue() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->testfile);
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    /**
     * @test
     */
    public function validFieldForSecondLineShouldReturnTrue() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->testfile);
        $sut->next();
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    /**
     * @test
     */
    public function validFieldForThirdLineShouldReturnFalse() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->testfile);
        $sut->next();
        $sut->next();
        $valid = $sut->valid();
        $this->assertFalse($valid);
    }

    /**
     * @test
     */
    public function afterARewindItShouldReturnFirstLine() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->testfile);
        $sut->next();
        $sut->rewind();
        $line = $sut->current();
        $this->assertEquals(array('first' => '1','second' => '2','third' => '3'), $line);
    }

    /**
     * @test
     */
    public function afterARewindAndANextItShouldReturnTheSecondElement() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->testfile);
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
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->testfile, null,0, ',');
        $line = $sut->current();
        $this->assertEquals(array('first;second;third' => '1;2;3'), $line);
    }

    /**
     * @test
     */
    public function loopingThroughShouldReturnAllElements() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->testfile);

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
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->testfile);
        $sut->skip(1);
        $line = $sut->current();
        $this->assertEquals(array('first' => '2','second' => '3','third' => '4'), $line);
    }

    /**
     * @test
     */
    public function skipTwoLineShouldReturnFalse() {
        $sut = new LoadIncrementalCsvDataIterator($this->configMock, $this->testfile);
        $sut->skip(2);
        $line = $sut->current();
        $this->assertFalse($line);
    }
}
