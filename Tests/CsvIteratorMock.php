<?php

class CsvIteratorMock {
    /**
     * Setup methods required to mock an iterator
     *
     * @param PHPUnit_Framework_MockObject_MockObject $iteratorMock The mock to attach the iterator methods to
     * @param array $items The mock data we're going to use with the iterator
     * @return PHPUnit_Framework_MockObject_MockObject The iterator mock
     */
    static public function get(TestCase $test, array $items)
    {
        $csvIteratorMock = $test->createMock(CsvIterator::class);

        $iteratorData = new \stdClass();
        $iteratorData->array = $items;
        $iteratorData->position = 0;

        $csvIteratorMock->expects($csvIteratorMock->any())
            ->method('rewind')
            ->will(
                $test->returnCallback(
                    function() use ($iteratorData) {
                        $iteratorData->position = 0;
                    }
                )
            );

        $csvIteratorMock->expects($test->any())
            ->method('current')
            ->will(
                $test->returnCallback(
                    function() use ($iteratorData) {
                        return $iteratorData->array[$iteratorData->position];
                    }
                )
            );

        $csvIteratorMock->expects($test->any())
            ->method('key')
            ->will(
                $test->returnCallback(
                    function() use ($iteratorData) {
                        return $iteratorData->position;
                    }
                )
            );

        $csvIteratorMock->expects($test->any())
            ->method('next')
            ->will(
                $test->returnCallback(
                    function() use ($iteratorData) {
                        $iteratorData->position++;
                    }
                )
            );

        $csvIteratorMock->expects($test->any())
            ->method('valid')
            ->will(
                $test->returnCallback(
                    function() use ($iteratorData) {
                        return isset($iteratorData->array[$iteratorData->position]);
                    }
                )
            );

        $csvIteratorMock->expects($test->any())
            ->method('count')
            ->will(
                $test->returnCallback(
                    function() use ($iteratorData) {
                        return sizeof($iteratorData->array);
                    }
                )
            );

        return $csvIteratorMock;
    }
}