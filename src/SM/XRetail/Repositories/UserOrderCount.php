<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 10/01/2017
 * Time: 09:31
 */

namespace SM\XRetail\Repositories;


use Magento\Framework\DataObject;
use SM\Core\Api\Data\XUserOrderCount;
use SM\XRetail\Helper\DataConfig;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

class UserOrderCount extends ServiceAbstract {

    protected $orderCountFactory;

    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SM\XRetail\Model\ResourceModel\UserOrderCounter\CollectionFactory $orderCountFactory
    ) {
        $this->orderCountFactory = $orderCountFactory;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    public function getUserOrderCount() {
        return $this->loadOrderCount($this->getSearchCriteria())->getOutput();
    }

    public function loadOrderCount($searchCriteria) {
        if (is_null($searchCriteria) || !$searchCriteria)
            $searchCriteria = $this->getSearchCriteria();

        $collection = $this->getOrderCountCollection($searchCriteria);

        $items = [];
        if ($collection->getLastPageNumber() < $searchCriteria->getData('currentPage')) {
        }
        else {

            foreach ($collection as $item) {
                $xUserOrderCount = new XUserOrderCount($item->getData());
                $items[]         = $xUserOrderCount;
            }
        }

        return $this->getSearchResult()
                    ->setSearchCriteria($searchCriteria)
                    ->setItems($items)
                    ->setTotalCount($collection->getSize())
                    ->setLastPageNumber($collection->getLastPageNumber());
    }

    protected function getOrderCountCollection(DataObject $searchCriteria) {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->orderCountFactory->create();
        $collection->setCurPage(is_nan($searchCriteria->getData('currentPage')) ? 1 : $searchCriteria->getData('currentPage'));
        $collection->setPageSize(
            is_nan($searchCriteria->getData('pageSize')) ? DataConfig::PAGE_SIZE_LOAD_DATA : $searchCriteria->getData('pageSize')
        );

        return $collection;
    }
}