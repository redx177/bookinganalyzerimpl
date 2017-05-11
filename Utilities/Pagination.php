<?php

class Pagination {
//    private $bookingsProvider;
    private $pageSize;

    /**
     * Pagination constructor.
     * @param ConfigProvider $config Configuration provider.
     * @param BookingsProvider $bookingsProvider Data provider to paginate.
     */
    public function __construct(ConfigProvider $config)
    {
        $this->pageSize = $config->get('pageSize');
    }

    /**
     * Gets the current page from the GET parameters.
     * @return int Current page.
     */
    public function getCurrentPage()
    {
        if (!array_key_exists('page', $_REQUEST)) {
            return 1;
        }
        return $this->validatePageNumber($_REQUEST['page']);
    }

    private function validatePageNumber($currentPage)
    {
        if ($currentPage < 1) {
            return 1;
        }
        return $currentPage;
    }

    /**
     * Gets the page size.
     * @return int Page size
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Gets the index of the first item in the current page.
     * Index calculation is starting with 0.
     * @return int The index of the first item on the current page.
     */
    public function getCurrentPageFirstItemIndex()
    {
        return $this->getPageSize() * ($this->getCurrentPage()-1);
    }

    /**
     * Because of the nature of iterators, there is no total count of items.
     * This method fixes the index of the data by comparing them to the provided
     * page in the GET parameters.
     * @param int $currentFirstIndexOnPage Index of the first item on the page.
     * @return int Fixed current page.
     */
    public function fixPageValue(int $currentFirstIndexOnPage)
    {
        $providedPage = $this->getCurrentPage();
        $calculatedFirstIndexOnPage = ($providedPage - 1) * $this->getPageSize();
        if ($currentFirstIndexOnPage == $calculatedFirstIndexOnPage) {
            return $providedPage;
        }
        return (int)($currentFirstIndexOnPage / $this->getPageSize())+1;

    }
}