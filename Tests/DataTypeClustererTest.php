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
                'intA' => '5', 'intB' => 5, 'intC' => '-3',  'intD' => -3, 'intE' => array(1,2,3),
                'distA' => '', 'distB' => 'close', 'distC' => 'invalid value',
                'priA' => '', 'priB' => 'luxury', 'priC' => 'budget', 'priD' => 'invalid value',
            ];
        $parameterMap = [
            ['floatFields',['floatA','floatB']],
            ['stringFields',['strA','strB','strC']],
            ['booleanFields',['boolA','boolB','boolC','boolD','boolE','boolF','boolG','boolH']],
            ['integerFields',['id','intA','intB','intC','intD','intE']],
            ['distanceFields',['distA','distB','distC']],
            ['priceFields',['priA','priB','priC','priD']],
        ];
        $configMock = $this->createMock(ConfigProvider::class);
        $configMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($parameterMap));

        $sut = new DataTypeClusterer($configMock);

        $data = $sut->get($rawData);

        $this->assertEquals('floatA', array_values($data->getFloatFields())[0]->getName());
        $this->assertEquals(33.22, array_values($data->getFloatFields())[0]->getValue());
        $this->assertEquals('floatB', array_values($data->getFloatFields())[1]->getName());
        $this->assertEquals(22.33, array_values($data->getFloatFields())[1]->getValue());

        $this->assertEquals('strA', array_values($data->getStringFields())[0]->getName());
        $this->assertEquals('CH12.12.12', array_values($data->getStringFields())[0]->getValue());
        $this->assertEquals('strB', array_values($data->getStringFields())[1]->getName());
        $this->assertEquals('aaabbbccc', array_values($data->getStringFields())[1]->getValue());
        $this->assertEquals('strC', array_values($data->getStringFields())[2]->getName());
        $this->assertEquals('', array_values($data->getStringFields())[2]->getValue());

        $this->assertEquals('boolA', array_values($data->getBooleanFields())[0]->getName());
        $this->assertEquals(true, array_values($data->getBooleanFields())[0]->getValue());
        $this->assertEquals('boolB', array_values($data->getBooleanFields())[1]->getName());
        $this->assertEquals(true, array_values($data->getBooleanFields())[1]->getValue());
        $this->assertEquals('boolC', array_values($data->getBooleanFields())[2]->getName());
        $this->assertEquals(true, array_values($data->getBooleanFields())[2]->getValue());
        $this->assertEquals('boolD', array_values($data->getBooleanFields())[3]->getName());
        $this->assertEquals(true, array_values($data->getBooleanFields())[3]->getValue());
        $this->assertEquals('boolE', array_values($data->getBooleanFields())[4]->getName());
        $this->assertEquals(false, array_values($data->getBooleanFields())[4]->getValue());
        $this->assertEquals('boolF', array_values($data->getBooleanFields())[5]->getName());
        $this->assertEquals(false, array_values($data->getBooleanFields())[5]->getValue());
        $this->assertEquals('boolG', array_values($data->getBooleanFields())[6]->getName());
        $this->assertEquals(false, array_values($data->getBooleanFields())[6]->getValue());
        $this->assertEquals('boolH', array_values($data->getBooleanFields())[7]->getName());
        $this->assertEquals(false, array_values($data->getBooleanFields())[7]->getValue());

        $this->assertEquals('id', array_values($data->getIntegerFields())[0]->getName());
        $this->assertEquals(34, array_values($data->getIntegerFields())[0]->getValue());
        $this->assertEquals('intA', array_values($data->getIntegerFields())[1]->getName());
        $this->assertEquals(5, array_values($data->getIntegerFields())[1]->getValue());
        $this->assertEquals('intB', array_values($data->getIntegerFields())[2]->getName());
        $this->assertEquals(5, array_values($data->getIntegerFields())[2]->getValue());
        $this->assertEquals('intC', array_values($data->getIntegerFields())[3]->getName());
        $this->assertEquals(-3, array_values($data->getIntegerFields())[3]->getValue());
        $this->assertEquals('intD', array_values($data->getIntegerFields())[4]->getName());
        $this->assertEquals(-3, array_values($data->getIntegerFields())[4]->getValue());
        $this->assertEquals('intE', array_values($data->getIntegerFields())[5]->getName());
        $this->assertEquals([1,2,3], array_values($data->getIntegerFields())[5]->getValue());

        $this->assertEquals('distA', array_values($data->getDistanceFields())[0]->getName());
        $this->assertEquals(Distance::Empty, array_values($data->getDistanceFields())[0]->getValue());
        $this->assertEquals('distB', array_values($data->getDistanceFields())[1]->getName());
        $this->assertEquals(Distance::Close, array_values($data->getDistanceFields())[1]->getValue());
        $this->assertEquals('distC', array_values($data->getDistanceFields())[2]->getName());
        $this->assertEquals(Distance::Empty, array_values($data->getDistanceFields())[2]->getValue());

        $this->assertEquals('priA', array_values($data->getPriceFields())[0]->getName());
        $this->assertEquals(Price::Empty, array_values($data->getPriceFields())[0]->getValue());
        $this->assertEquals('priB', array_values($data->getPriceFields())[1]->getName());
        $this->assertEquals(Price::Luxury, array_values($data->getPriceFields())[1]->getValue());
        $this->assertEquals('priC', array_values($data->getPriceFields())[2]->getName());
        $this->assertEquals(Price::Budget, array_values($data->getPriceFields())[2]->getValue());
        $this->assertEquals('priD', array_values($data->getPriceFields())[3]->getName());
        $this->assertEquals(Price::Empty, array_values($data->getPriceFields())[3]->getValue());
    }
}
