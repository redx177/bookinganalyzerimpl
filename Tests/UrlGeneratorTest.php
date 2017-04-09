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
                    new DataTypeCluster(
                        ['a1' => new IntegerField('a1', 'a1a'), 'a2' => new IntegerField('a2', ['a2a', 'a2b'])],
                        ['b' => new BooleanField('b', true)],
                        ['c' => new FloatField('c', 2.2)],
                        ['d' => new StringField('d', 'd')],
                        ['e1' => new PriceField('e1', Price::Budget),
                            'e2' => new PriceField('e2', Price::Luxury),
                            'e3' => new PriceField('e3', Price::Empty)],
                        ['f1' => new DistanceField('f1', Distance::Close),
                            'f2' => new DistanceField('f2', Distance::Empty)]));
        $sut = new UrlGenerator();

        $urlParams = $sut->getParameters($filters);
        $this->assertEquals('action=myAction&a1=a1a&a2[]=a2a&a2[]=a2b&b=1&c=2.2&d=d&e1=budget&e2=luxury&f1=close', $urlParams);
    }

    /**
     * @test
     */
    public function missingParametersShouldNotGenerateAdditionalAmpersands() {
        $filters = new Filters(
            'myAction',
            new DataTypeCluster([], [], ['c' => new FloatField('c', 2.2)],[], [],[]));
        $sut = new UrlGenerator();

        $urlParams = $sut->getParameters($filters);
        $this->assertEquals('action=myAction&c=2.2', $urlParams);
    }
}
