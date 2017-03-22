<?php
use \PHPUnit\Framework\TestCase,
    org\bovigo\vfs\vfsStream;

require_once dirname(__DIR__) . "/Utilities/CsvIterator.php";

class CsvIteratorTest extends TestCase
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

    public function testValidFileShouldBeOpened() {
        new CsvIterator($this->testfile);
        $this->assertTrue(true);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidFileShouldThrowException() {
        new CsvIterator("invalidFile");
    }

    public function testGettingFirstLineShouldReturnCorrectData() {
        $sut = new CsvIterator($this->testfile);
        $line = $sut->current();
        $this->assertEquals(array('first' => '1','second' => '2','third' => '3'), $line);
    }

    public function testGettingSecondLineShouldReturnCorrectData() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $line = $sut->current();
        $this->assertEquals(array('first' => '2','second' => '3','third' => '4'), $line);
    }

    public function testGettingThirdLineShouldReturnFalse() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $sut->next();
        $line = $sut->current();
        $this->assertFalse($line);
    }

    public function testValidFieldOnFirstLineShouldReturnTrue() {
        $sut = new CsvIterator($this->testfile);
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    public function testValidFieldForSecondLineShouldReturnTrue() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    public function testValidFieldForThirdLineShouldReturnFalse() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $sut->next();
        $valid = $sut->valid();
        $this->assertFalse($valid);
    }

    public function testAfterARewindItShouldReturnFirstLine() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $sut->rewind();
        $line = $sut->current();
        $this->assertEquals(array('first' => '1','second' => '2','third' => '3'), $line);
    }

    public function testAfterARewindAndANextItShouldReturnTheSecondElement() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $sut->next();
        $sut->rewind();
        $sut->next();
        $line = $sut->current();
        $this->assertEquals(array('first' => '2','second' => '3','third' => '4'), $line);
    }

    public function testProvidingDiffernentDeliminiterShouldReturnSingleString() {
        $sut = new CsvIterator($this->testfile, 0, ',');
        $line = $sut->current();
        $this->assertEquals(array('first;second;third' => '1;2;3'), $line);
    }

    public function testLoopingThroughShouldReturnAllElements() {
        $sut = new CsvIterator($this->testfile);

        $result = array();
        foreach($sut as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertEquals(array(
            2 => array('first' => '1','second' => '2','third' => '3'),
            3 => array('first' => '2','second' => '3','third' => '4'),
        ), $result);
    }

    public function testSkipOneLineShouldReturnSecond() {
        $sut = new CsvIterator($this->testfile);
        $sut->skip(1);
        $line = $sut->current();
        $this->assertEquals(array('first' => '2','second' => '3','third' => '4'), $line);
    }

    public function testSkipTwoLineShouldReturnFalse() {
        $sut = new CsvIterator($this->testfile);
        $sut->skip(2);
        $line = $sut->current();
        $this->assertFalse($line);
    }
}
