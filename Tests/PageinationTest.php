<?php
require_once dirname(__DIR__) . '/Business/Pagination.php';
require_once dirname(__DIR__) . '/Business/DataProvider.php';
require_once dirname(__DIR__) . '/Utilities/ConfigProvider.php';
use PHPUnit\Framework\TestCase;

class PageinationTest extends TestCase
{
    private $configMock;
    private $dataProviderMock;

    protected function setUp()
    {
        $this->configMock = $this->createMock(ConfigProvider::class);
        $this->configMock->method('get')
            ->willReturn(4);

        $this->dataProviderMock = $this->createMock(DataProvider::class);
        $this->dataProviderMock->method('getItemCount')
            ->willReturn(22);
    }

    /**
     * @test
     */
    public function emptyPageParameterShouldReturnCurrentPage1() {
        $sut = new Pagination($this->configMock, $this->dataProviderMock);

        $this->assertEquals(1, $sut->getCurrentPage());
    }

    /**
     * @test
     */
    public function negativePageParameterShouldReturnCurrentPage1() {
        $_GET['page'] = -5;
        $sut = new Pagination($this->configMock, $this->dataProviderMock);

        $this->assertEquals(1, $sut->getCurrentPage());
    }

    /**
     * @test
     */
    public function pageParameter0ShouldReturnCurrentPage1() {
        $_GET['page'] = 0;
        $sut = new Pagination($this->configMock, $this->dataProviderMock);

        $this->assertEquals(1, $sut->getCurrentPage());
    }

    /**
     * @test
     */
    public function pageParameter2ShouldReturnCurrentPage2() {
        $_GET['page'] = 2;
        $sut = new Pagination($this->configMock, $this->dataProviderMock);

        $this->assertEquals($_GET['page'], $sut->getCurrentPage());
    }

    /**
     * @test
     */
    public function pageParameter99ShouldReturnCurrentPage6() {
        $_GET['page'] = 99;
        $sut = new Pagination($this->configMock, $this->dataProviderMock);

        $this->assertEquals(6, $sut->getCurrentPage());
    }

    /**
     * @test
     */
    public function pageSize3ConfigurationShouldReturnPageSize3() {
        $sut = new Pagination($this->configMock, $this->dataProviderMock);

        $this->assertEquals(4, $sut->getPageSize());
    }

    /**
     * @test
     */
    public function for5ItemsAndPageSize2ItShouldReturnPageCount3() {
        $sut = new Pagination($this->configMock, $this->dataProviderMock);

        $this->assertEquals(6, $sut->getPageCount());
    }

    /**
     * @test
     */
    public function currentPage0WithPageSize4ShouldReturnCurrentPageFirstItemIndex0() {
        $_GET['page'] = 0;

        $sut = new Pagination($this->configMock, $this->dataProviderMock);

        $this->assertEquals(0, $sut->getCurrentPageFirstItemIndex());
    }

    /**
     * @test
     */
    public function currentPage1WithPageSize4ShouldReturnCurrentPageFirstItemIndex0() {
        $_GET['page'] = 1;

        $sut = new Pagination($this->configMock, $this->dataProviderMock);

        $this->assertEquals(0, $sut->getCurrentPageFirstItemIndex());
    }

    /**
     * @test
     */
    public function currentPage3WithPageSize4ShouldReturnCurrentPageFirstItemIndex6() {
        $_GET['page'] = 3;

        $sut = new Pagination($this->configMock, $this->dataProviderMock);

        $this->assertEquals(8, $sut->getCurrentPageFirstItemIndex());
    }
}
