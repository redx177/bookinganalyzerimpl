<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__) . "/CsvIterator.php";

class CsvIteratorTest extends TestCase
{
    private $testfile;

    protected function setUp()
    {
        $this->testfile = __DIR__ . "/test.csv";
    }

    public function testValidFileCanBeOpened() {
        new CsvIterator($this->testfile);
        $this->assertTrue(true);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidFileThrowsAnException() {
        new CsvIterator("invalidFile");
    }

    public function testGettingFirstLineShouldReturnCorrectData() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $line = $sut->current();
        $this->assertEquals(array('first' => '1','second' => '2','third' => '3'), $line);
    }

    public function testGettingSecondLineShouldReturnCorrectData() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $sut->next();
        $line = $sut->current();
        $this->assertEquals(array('first' => '2','second' => '3','third' => '4'), $line);
    }

    public function testGettingThirdLineShouldReturnFalse() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $sut->next();
        $sut->next();
        $line = $sut->current();
        $this->assertFalse($line);
    }

    public function testValidFieldShouldBeTrueForFirstLine() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    public function testValidFieldShouldBeTrueForSecondLine() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $sut->next();
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    public function testValidFieldShouldBeFalseForThirdLine() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $sut->next();
        $sut->next();
        $valid = $sut->valid();
        $this->assertFalse($valid);
    }

    public function testCallingCurrentBeforeNextShouldReturnFalse() {
        $sut = new CsvIterator($this->testfile);
        $line = $sut->current();
        $this->assertFalse($line);
    }

    public function afterARewindItShouldReturnFalse() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $sut->rewind();
        $line = $sut->current();
        $this->assertFalse($line);
    }

    public function afterARewindAndANextItShouldReturnTheFirstElement() {
        $sut = new CsvIterator($this->testfile);
        $sut->next();
        $sut->next();
        $sut->rewind();
        $sut->next();
        $line = $sut->current();
        $this->assertEquals(array('first' => '1','second' => '2','third' => '3'), $line);
    }
}
