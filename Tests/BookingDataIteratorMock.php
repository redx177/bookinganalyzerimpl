<?php
use \PHPUnit\Framework\TestCase;

class BookingDataIteratorAdapterMock {
    /**
     * Setup methods required to mock an iterator
     *
     * @param TestCase $testCase Test case to create the mock.
     * @param array $items The mock data we're going to use with the iterator.
     * @return PHPUnit_Framework_MockObject_MockObject The iterator mock.
     */
    static public function get(TestCase $testCase, array $items)
    {

        $iteratorMock = $testCase->getMockBuilder(BookingDataIterator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $iteratorData = new \stdClass();
        $iteratorData->array = $items;
        $iteratorData->position = 0;

        $iteratorMock->expects($testCase->any())
            ->method('rewind')
            ->will(
                $testCase->returnCallback(
                    function() use ($iteratorData) {
                        $iteratorData->position = 0;
                    }
                )
            );

        $iteratorMock->expects($testCase->any())
            ->method('current')
            ->will(
                $testCase->returnCallback(
                    function() use ($iteratorData) {
                        return isset($iteratorData->array[$iteratorData->position])
                            ? $iteratorData->array[$iteratorData->position]
                            : null;
                    }
                )
            );

        $iteratorMock->expects($testCase->any())
            ->method('key')
            ->will(
                $testCase->returnCallback(
                    function() use ($iteratorData) {
                        return $iteratorData->position;
                    }
                )
            );

        $iteratorMock->expects($testCase->any())
            ->method('next')
            ->will(
                $testCase->returnCallback(
                    function() use ($iteratorData) {
                        $iteratorData->position++;
                    }
                )
            );

        $iteratorMock->expects($testCase->any())
            ->method('valid')
            ->will(
                $testCase->returnCallback(
                    function() use ($iteratorData) {
                        return isset($iteratorData->array[$iteratorData->position]);
                    }
                )
            );

        $iteratorMock->expects($testCase->any())
            ->method('skip')
            ->will(
                $testCase->returnCallback(
                    function($from) use ($iteratorData) {
                        $iteratorData->position = $from;
                    }
                )
            );

        $iteratorMock->expects($testCase->any())
            ->method('count')
            ->will(
                $testCase->returnCallback(
                    function() use ($iteratorData) {
                        return count($iteratorData->array);
                    }
                )
            );

        return $iteratorMock;
    }
}