<?php
require_once dirname(__DIR__) . '/Utilities/Pagination.php';
require_once dirname(__DIR__) . '/Utilities/ConfigProvider.php';
require_once dirname(__DIR__) . '/Business/DataProvider.php';
use PHPUnit\Framework\TestCase;

class PageinationTest extends TestCase
{
    /**
     * @test
     */
    public function emptyPageParameterShouldReturnCurrentPage1() {
        $config = $this->createMock(ConfigProvider::class);
        $dataProviderMock = $this->createMock(DataProvider::class);
        $sut = new Pagination($config, $dataProviderMock);

        $this->assertEquals(1, $sut->getCurrentPage());
    }

    /**
     * @test
     */
    public function negativePageParameterShouldReturnCurrentPage1() {
        $config = $this->createMock(ConfigProvider::class);
        $dataProviderMock = $this->createMock(DataProvider::class);
        $_GET['page'] = -5;
        $sut = new Pagination($config, $dataProviderMock);

        $this->assertEquals(1, $sut->getCurrentPage());
    }

    /**
     * @test
     */
    public function pageParameter0ShouldReturnCurrentPage1() {
        $config = $this->createMock(ConfigProvider::class);
        $dataProviderMock = $this->createMock(DataProvider::class);
        $_GET['page'] = 0;
        $sut = new Pagination($config, $dataProviderMock);

        $this->assertEquals(1, $sut->getCurrentPage());
    }

    /**
     * @test
     */
    public function pageParameter2ShouldReturnCurrentPage2() {
        $config = $this->createMock(ConfigProvider::class);
        $dataProviderMock = $this->createMock(DataProvider::class);
        $_GET['page'] = 2;
        $sut = new Pagination($config, $dataProviderMock);

        $this->assertEquals($_GET['page'], $sut->getCurrentPage());
    }

    /**
     * @test
     */
    public function pageSize3ConfigurationShouldReturnPageSize3() {
        $pageSize = 3;

        $config = $this->createMock(ConfigProvider::class);
        $config->method('get')
            ->willReturn($pageSize);
        $dataProviderMock = $this->createMock(DataProvider::class);

        $sut = new Pagination($config, $dataProviderMock);

        $this->assertEquals($pageSize, $sut->getPageSize());
    }

    /**
     * @test
     */
    public function for5ItemsAndPageSize2ItShouldReturnPageCount3() {
        $config = $this->createMock(ConfigProvider::class);
        $config->method('get')
            ->willReturn(2);
        $dataProviderMock = $this->createMock(DataProvider::class);
        $dataProviderMock->method('getItemCount')
            ->willReturn(5);

        $sut = new Pagination($config, $dataProviderMock);

        $this->assertEquals(3, $sut->getPageCount());
    }

    /**
     * @test
     */
    public function currentPage0WithPageSize4ShouldReturnCurrentPageFirstItemIndex0() {
        $config = $this->createMock(ConfigProvider::class);
        $config->method('get')
            ->willReturn(4);
        $dataProviderMock = $this->createMock(DataProvider::class);
        $dataProviderMock->method('getItemCount')
            ->willReturn(20);
        $_GET['page'] = 0;

        $sut = new Pagination($config, $dataProviderMock);

        $this->assertEquals(0, $sut->getCurrentPageFirstItemIndex());
    }

    /**
     * @test
     */
    public function currentPage1WithPageSize4ShouldReturnCurrentPageFirstItemIndex0() {
        $config = $this->createMock(ConfigProvider::class);
        $config->method('get')
            ->willReturn(4);
        $dataProviderMock = $this->createMock(DataProvider::class);
        $dataProviderMock->method('getItemCount')
            ->willReturn(20);
        $_GET['page'] = 1;

        $sut = new Pagination($config, $dataProviderMock);

        $this->assertEquals(0, $sut->getCurrentPageFirstItemIndex());
    }

    /**
     * @test
     */
    public function currentPage3WithPageSize4ShouldReturnCurrentPageFirstItemIndex6() {
        $config = $this->createMock(ConfigProvider::class);
        $config->method('get')
            ->willReturn(4);
        $dataProviderMock = $this->createMock(DataProvider::class);
        $dataProviderMock->method('getItemCount')
            ->willReturn(20);
        $_GET['page'] = 3;

        $sut = new Pagination($config, $dataProviderMock);

        $this->assertEquals(8, $sut->getCurrentPageFirstItemIndex());
    }
}
