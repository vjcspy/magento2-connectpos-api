<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 5/16/17
 * Time: 3:19 PM
 */

namespace SM\Performance\Observer\ProductCaching;

use Magento\Framework\ObjectManagerInterface;
use SM\Core\Api\Data\XProduct;
use SM\Integrate\Model\WarehouseIntegrateManagement;
use SM\Performance\Helper\CacheKeeper;

class BeforeLoading implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \SM\Performance\Helper\CacheKeeper
     */
    protected $cacheKeeper;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * BeforeLoading constructor.
     *
     * @param \SM\Performance\Helper\CacheKeeper        $cacheKeeper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\SM\Performance\Helper\CacheKeeper $cacheKeeper, ObjectManagerInterface $objectManager) {
        $this->cacheKeeper   = $cacheKeeper;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $loadingData = $observer->getData('loading_data');
        /** @var \Magento\Framework\DataObject $searchCriteria */
        $searchCriteria = $loadingData->getData('search_criteria');
        $storeId        = $searchCriteria->getData('storeId');
        $warehouseId    = WarehouseIntegrateManagement::getWarehouseId();

        $cacheInfo = $this->cacheKeeper->getCacheInstanceInfo($storeId, $warehouseId);

        $currentPage = $searchCriteria->getData('currentPage');
        $pageSize    = $searchCriteria->getData('pageSize');

        if (!$cacheInfo || !\SM\Performance\Helper\CacheKeeper::$USE_CACHE) {
            return;
        }

        $isRealTime = floatval($searchCriteria->getData('realTime')) == 1;
        $cacheTime  = $searchCriteria->getData('cache_time');

        if ($isRealTime) {
            if (!$cacheTime || is_nan($cacheTime)) {
                throw new \Exception("Realtime must have param cache_time and cache time must be number");
            }

            if (floatval($cacheInfo->getData('cache_time')) < floatval($cacheTime) || boolval($cacheInfo->getData('is_over')) !== true) {
                return;
            }
            else {
                /** @var \SM\Performance\Model\AbstractProductCache $cacheInstance */
                $cacheInstance = $this->cacheKeeper->getInstance($storeId, $warehouseId);
                $collection    = $cacheInstance->getCollection();

                if ($searchCriteria->getData('entity_id') || $searchCriteria->getData('entityId')) {
                    $ids = is_null($searchCriteria->getData('entity_id'))
                        ? $searchCriteria->getData('entityId')
                        : $searchCriteria->getData(
                            'entity_id');
                    $collection->addFieldToFilter('id', ['in' => explode(",", $ids)]);
                }
                $loadingData->setData(CacheKeeper::$IS_PULL_FROM_CACHE, true);
                $loadingData->setData('collection', $collection);
                $loadingData->setData('items', $this->retrieveDataFromCollection($collection));
            }
        }
        else if (($cacheInfo && boolval($cacheInfo->getData('is_over')) === true)
                 || ($currentPage <= $cacheInfo->getData('current_page')
                     && intval($pageSize) === intval($cacheInfo->getData('page_size'))
                     && $searchCriteria->getData('productIds') === null
                     && $searchCriteria->getData('entityId') === null
                     && $searchCriteria->getData('entity_id') === null)
        ) {
            /** @var \SM\Performance\Model\AbstractProductCache $cacheInstance */
            $cacheInstance = $this->cacheKeeper->getInstance($storeId, $warehouseId);

            if (!$cacheInstance) {
                throw new \Exception("Error SM\\Performance\\Observer\\ProductCaching\\BeforeLoading");
            }

            $collection = $cacheInstance->getCollection();
            $collection->setCurPage($currentPage);
            if ($searchCriteria->getData('productIds')) {
                $collection->addFieldToFilter('id', ['in' => $searchCriteria->getData('productIds')]);
            }
            if ($searchCriteria->getData('entity_id') || $searchCriteria->getData('entityId')) {
                $ids = is_null($searchCriteria->getData('entity_id')) ? $searchCriteria->getData('entityId') : $searchCriteria->getData('entity_id');
                $collection->addFieldToFilter('id', ['in' => explode(",", $ids)]);
            }

            if ($cacheInfo && boolval($cacheInfo->getData('is_over')) === true) {
                $collection->setPageSize(500);
            }
            else {
                $collection->setPageSize($pageSize);
            }

            $loadingData->setData(CacheKeeper::$IS_PULL_FROM_CACHE, true);
            $loadingData->setData('collection', $collection);

            if ($collection->getLastPageNumber() < $currentPage) {
                $loadingData->setData('items', []);
            }
            else {
                $loadingData->setData('items', $this->retrieveDataFromCollection($collection));
            }

            $loadingData->setData('cache_time', $cacheInfo->getData('cache_time'));
        }
    }

    /**
     * @param $collection
     *
     * @return array
     */
    protected function retrieveDataFromCollection($collection) {
        $items = [];
        foreach ($collection as $item) {
            $itemData = json_decode($item->getData('data'), true);
            if (is_array($itemData)) {
                $xProduct = new XProduct($itemData);
                $items[]  = $xProduct;
            }
        }

        return $items;
    }
}
