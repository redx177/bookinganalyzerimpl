<?php
require_once dirname(__DIR__) . "/Interfaces/BookingDataIterator.php";
require_once dirname(__DIR__) . "/Utilities/LoadRedisDataIterator.php";

use \PHPUnit\Framework\TestCase;

class LoadRedisDataIteratorTest extends TestCase
{
    private $redisMock;

    protected function setUp()
    {
        //$this->redisMock = $this->createMock(Redis::class);
        $this->redisMock = $this->getMockBuilder(Redis::class)
            ->setMethods(['hGetAll'])
            ->disableOriginalConstructor()
            ->getMock();

        $map = [
            [1, [101]],
            [2, [102]]
        ];

        $this->redisMock
            ->method('hGetAll')
            ->will($this->returnValueMap($map));
    }

    /**
     * @test
     */
    public function gettingFirstLineShouldReturnCorrectData() {
        $this->redisMock
            ->expects($this->once())
            ->method('hGetAll')
            ->with($this->equalTo(1));

        $sut = new LoadRedisDataIterator($this->redisMock);
        $line = $sut->current();
        $this->assertEquals([101], $line);
    }

    /**
     * @test
     */
    public function gettingSecondLineShouldReturnCorrectData() {
        $this->redisMock
            ->expects($this->exactly(2))
            ->method('hGetAll');

        $sut = new LoadRedisDataIterator($this->redisMock);
        $sut->next();
        $line = $sut->current();
        $this->assertEquals([102], $line);
    }

    /**
     * @test
     */
    public function gettingThirdLineShouldReturnFalse() {
        $this->redisMock
            ->expects($this->exactly(3))
            ->method('hGetAll');

        $sut = new LoadRedisDataIterator($this->redisMock);
        $sut->next();
        $sut->next();
        $line = $sut->current();
        $this->assertFalse($line);
    }

    /**
     * @test
     */
    public function validFieldOnFirstLineShouldReturnTrue() {
        $sut = new LoadRedisDataIterator($this->redisMock);
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    /**
     * @test
     */
    public function validFieldForSecondLineShouldReturnTrue() {
        $sut = new LoadRedisDataIterator($this->redisMock);
        $sut->next();
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    /**
     * @test
     */
    public function validFieldForForthLineShouldReturnFalse() {
        $sut = new LoadRedisDataIterator($this->redisMock);
        $sut->next();
        $sut->next();
        $sut->next();
        $valid = $sut->valid();
        $this->assertFalse($valid);
    }

    /**
     * @test
     */
    public function afterARewindItShouldReturnFirstLine() {
        $sut = new LoadRedisDataIterator($this->redisMock);
        $sut->next();
        $sut->rewind();
        $line = $sut->current();
        $this->assertEquals([101], $line);
    }

    /**
     * @test
     */
    public function afterARewindAndANextItShouldReturnTheSecondElement() {
        $sut = new LoadRedisDataIterator($this->redisMock);
        $sut->next();
        $sut->next();
        $sut->rewind();
        $sut->next();
        $line = $sut->current();
        $this->assertEquals([102], $line);
    }

     /**
     * @test
     */
    public function loopingThroughShouldReturnAllElements() {
        $sut = new LoadRedisDataIterator($this->redisMock);

        $result = [];
        foreach($sut as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertEquals([
            1 => [101],
            2 => [102],
        ], $result);
    }
}
