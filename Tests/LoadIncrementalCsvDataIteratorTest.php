<?php
use \PHPUnit\Framework\TestCase,
    org\bovigo\vfs\vfsStream;

require_once dirname(__DIR__) . "/Interfaces/BookingDataIterator.php";
require_once dirname(__DIR__) . "/Utilities/LoadIncrementalCsvDataIterator.php";

class LoadIncrementalCsvDataIteratorTest extends TestCase
{
    private $testfile;

    protected function setUp()
    {
        $this->testfile = 'home/test.csv';

        // Creating mock data file with vfs (virtual file system).
        vfsStream::setup('home');
        $this->testfile = vfsStream::url('home/test.csv');
        file_put_contents($this->testfile, 'first;second;third
1;2;3
2;3;4');
    }

    /**
     * @test
     */
    public function validFileShouldBeOpened() {
        new LoadIncrementalCsvDataIterator($this->testfile);
        $this->assertTrue(true);
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function invalidFileShouldThrowException() {
        new LoadIncrementalCsvDataIterator("invalidFile");
    }

    /**
     * @test
     */
    public function gettingFirstLineShouldReturnCorrectData() {
        $sut = new LoadIncrementalCsvDataIterator($this->testfile);
        $line = $sut->current();
        $this->assertEquals(array('first' => '1','second' => '2','third' => '3'), $line);
    }

    /**
     * @test
     */
    public function gettingSecondLineShouldReturnCorrectData() {
        $sut = new LoadIncrementalCsvDataIterator($this->testfile);
        $sut->next();
        $line = $sut->current();
        $this->assertEquals(array('first' => '2','second' => '3','third' => '4'), $line);
    }

    /**
     * @test
     */
    public function gettingThirdLineShouldReturnFalse() {
        $sut = new LoadIncrementalCsvDataIterator($this->testfile);
        $sut->next();
        $sut->next();
        $line = $sut->current();
        $this->assertFalse($line);
    }

    /**
     * @test
     */
    public function validFieldOnFirstLineShouldReturnTrue() {
        $sut = new LoadIncrementalCsvDataIterator($this->testfile);
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    /**
     * @test
     */
    public function validFieldForSecondLineShouldReturnTrue() {
        $sut = new LoadIncrementalCsvDataIterator($this->testfile);
        $sut->next();
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    /**
     * @test
     */
    public function validFieldForThirdLineShouldReturnFalse() {
        $sut = new LoadIncrementalCsvDataIterator($this->testfile);
        $sut->next();
        $sut->next();
        $valid = $sut->valid();
        $this->assertFalse($valid);
    }

    /**
     * @test
     */
    public function afterARewindItShouldReturnFirstLine() {
        $sut = new LoadIncrementalCsvDataIterator($this->testfile);
        $sut->next();
        $sut->rewind();
        $line = $sut->current();
        $this->assertEquals(array('first' => '1','second' => '2','third' => '3'), $line);
    }

    /**
     * @test
     */
    public function afterARewindAndANextItShouldReturnTheSecondElement() {
        $sut = new LoadIncrementalCsvDataIterator($this->testfile);
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
        $sut = new LoadIncrementalCsvDataIterator($this->testfile, 0, ',');
        $line = $sut->current();
        $this->assertEquals(array('first;second;third' => '1;2;3'), $line);
    }

    /**
     * @test
     */
    public function loopingThroughShouldReturnAllElements() {
        $sut = new LoadIncrementalCsvDataIterator($this->testfile);

        $result = [];
        foreach($sut as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertEquals(array(
            1 => array('first' => '1','second' => '2','third' => '3'),
            2 => array('first' => '2','second' => '3','third' => '4'),
        ), $result);
    }

    public function testSkipOneLineShouldReturnSecond() {
        $sut = new LoadIncrementalCsvDataIterator($this->testfile);
        $sut->skip(1);
        $line = $sut->current();
        $this->assertEquals(array('first' => '2','second' => '3','third' => '4'), $line);
    }

    public function testSkipTwoLineShouldReturnFalse() {
        $sut = new LoadIncrementalCsvDataIterator($this->testfile);
        $sut->skip(2);
        $line = $sut->current();
        $this->assertFalse($line);
    }
}
