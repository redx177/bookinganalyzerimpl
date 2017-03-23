<?php

class Pagination {
    private $dataProvider;
    private $pageSize;

    /**
     * Pagination constructor.
     * @param ConfigProvider $config Configuration provider.
     * @param DataProvider $dataProvider Data provider to paginate.
     * @param int $currentPage Current page as provided by the frontend.
     */
    public function __construct(ConfigProvider $config, DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->pageSize = $config->get('pageSize');
    }

    /**
     * Gets the current page.
     * @return int
     */
    public function getCurrentPage()
    {
        if (!array_key_exists('page', $_GET)) {
            return 1;
        }
        return $this->validatePageNumber($_GET['page']);
    }

    private function validatePageNumber($currentPage)
    {
        if ($currentPage < 1) {
            return 1;
        }
        return $currentPage;
    }

    /**
     * Calculates the total page count.
     * @return float
     */
    public function getPageCount()
    {
        $itemCount = $this->dataProvider->getItemCount();
        return ceil($itemCount / $this->pageSize);
    }

    /**
     * Gets the page size.
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Gets the index of the first item in the current page.
     * Index calculation is starting with 0.
     */
    public function getCurrentPageFirstItemIndex()
    {
        return $this->getPageSize() * ($this->getCurrentPage()-1);
    }
}