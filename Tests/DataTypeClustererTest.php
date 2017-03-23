<?php
require_once dirname(__DIR__) . "/Business/DataTypeClusterer.php";
require_once dirname(__DIR__) . "/Models/DataTypeCluster.php";
require_once dirname(__DIR__) . "/Models/Distance.php";
require_once dirname(__DIR__) . "/Models/Price.php";
require_once dirname(__DIR__) . "/Utilities/ConfigProvider.php";

use PHPUnit\Framework\TestCase;

class DataTypeClustererTest extends TestCase
{
    /**
     * @test
     */
    public function fieldsShouldBeInTheCorrespondingTypedFields() {
        $rawData = [
                'id' => '34',
                'floatA' => '33.22', 'floatB' => 22.33,
                'strA' => 'CH12.12.12', 'strB' => 'aaabbbccc', 'strC' => '',
                'boolA' => '1.0', 'boolB' => '1', 'boolC' => 1, 'boolD' => true,
                    'boolE' => '0.0', 'boolF' => '0', 'boolG' => 0, 'boolH' => false,
                'intA' => '5', 'intB' => 5, 'intC' => '-3',  'intD' => -3,
                'distA' => '', 'distB' => 'close', 'distC' => 'invalid value',
                'priA' => '', 'priB' => 'luxury', 'priC' => 'budget', 'priD' => 'invalid value',
            ];
        $parameterMap = [
            ['idField','id'],
            ['floatFields',['floatA','floatB']],
            ['stringFields',['strA','strB','strC']],
            ['booleanFields',['boolA','boolB','boolC','boolD','boolE','boolF','boolG','boolH']],
            ['integerFields',['intA','intB','intC','intD']],
            ['distanceFields',['distA','distB','distC']],
            ['priceFields',['priA','priB','priC','priD']],
        ];
        $configMock = $this->createMock(ConfigProvider::class);
        $configMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($parameterMap));

        $sut = new DataTypeClusterer($configMock);

        $data = $sut->get($rawData);

        $this->assertEquals(34, $data->getId());
        $this->assertEquals(['floatA' => 33.22, 'floatB' => 22.33], $data->getFloatFields());
        $this->assertEquals(['strA' => 'CH12.12.12', 'strB' => 'aaabbbccc', 'strC' => ''], $data->getStringFields());
        $this->assertEquals(['boolA' => true, 'boolB' => true, 'boolC' => true, 'boolD' => true,
            'boolE' => false, 'boolF' => false, 'boolG' => false, 'boolH' => false], $data->getBooleanFields());
        $this->assertEquals(['intA' => 5, 'intB' => 5, 'intC' => -3, 'intD' => -3], $data->getIntegerFields());
        $this->assertEquals(['distA' => Distance::Empty, 'distB' => Distance::Close, 'distC' => Distance::Empty], $data->getDistanceFields());
        $this->assertEquals(['priA' => Price::Empty, 'priB' => Price::Luxury, 'priC' => Price::Budget, 'priD' => Price::Empty], $data->getPriceFields());
    }
}
