<?php
require_once dirname(__DIR__) . '/Utilities/UrlGenerator.php';
require_once dirname(__DIR__) . '/Models/Filters.php';
require_once dirname(__DIR__) . '/Models/DataTypeCluster.php';
require_once dirname(__DIR__) . '/Models/Price.php';
require_once dirname(__DIR__) . '/Models/Distance.php';

use PHPUnit\Framework\TestCase;

class UrlGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function getParametersShouldCreateValidParameterString() {
        $filters = new Filters(
            'myAction',
            new DataTypeCluster(['a1' => 'a1a', 'a2' => ['a2a', 'a2b']], ['b' => 'b'], ['c' => 'c'],
                ['d' => 'd'], ['e1' => Price::Budget, 'e2' => Price::Luxury, 'e3' => Price::Empty],
                ['f1' => Distance::Close, 'f2' => Distance::Empty]));
        $sut = new UrlGenerator();

        $urlParams = $sut->getParameters($filters);
        $this->assertEquals('action=myAction&a1=a1a&a2[]=a2a&a2[]=a2b&b=b&c=c&d=d&e1=budget&e2=luxury&f1=close', $urlParams);
    }

    /**
     * @test
     */
    public function missingParametersShouldNotGenerateAdditionalAmpersands() {
        $filters = new Filters(
            'myAction',
            new DataTypeCluster([], [], ['c' => 'c'],[], [],[]));
        $sut = new UrlGenerator();

        $urlParams = $sut->getParameters($filters);
        $this->assertEquals('action=myAction&c=c', $urlParams);
    }
}
