<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 5/16/17
 * Time: 3:19 PM
 */

namespace SM\Performance\Observer\ProductCaching;


use Magento\Framework\Event\Observer;
use Magento\Framework\ObjectManagerInterface;
use SM\Integrate\Model\WarehouseIntegrateManagement;
use SM\Performance\Helper\CacheKeeper;

/**
 * Class AfterLoading
 *
 * @package SM\Performance\Observer\ProductCaching
 */
class AfterLoading implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \SM\Performance\Helper\CacheKeeper
     */
    protected $cacheKeeper;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * AfterLoading constructor.
     *
     * @param \SM\Performance\Helper\CacheKeeper        $cacheKeeper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \SM\Performance\Helper\CacheKeeper $cacheKeeper,
        ObjectManagerInterface $objectManager
    ) {
        $this->cacheKeeper   = $cacheKeeper;
        $this->objectManager = $objectManager;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        // TODO: Implement execute() method.
        $loadingData = $observer->getData('loading_data');
        /** @var \Magento\Framework\DataObject $searchCriteria */
        $searchCriteria = $loadingData->getData('search_criteria');
        $storeId        = $searchCriteria->getData('storeId');
        $warehouseId    = WarehouseIntegrateManagement::getWarehouseId();

        $this->cacheKeeper->getInstance($storeId, $warehouseId);
        $cacheInfo = $this->cacheKeeper->getCacheInstanceInfo($storeId, $warehouseId);

        if ($loadingData->getData(CacheKeeper::$IS_PULL_FROM_CACHE) !== true) {
            /** @var \SM\Core\Api\Data\XProduct[] $items */
            $items = $loadingData->getData('items');

            foreach ($items as $item) {
                $cacheInstance = $this->cacheKeeper->getInstance($storeId, $warehouseId);
                try {
                    $cacheInstance->setData('id', $item->getId())
                                  ->setData('data', json_encode($item->getData()))
                                  ->save();
                }
                catch (\Exception $e) {

                }
            }
        }

        $cacheInfo->setData('cache_time', CacheKeeper::getCacheTime());
        $cacheInfo->setData('page_size', $searchCriteria->getData('pageSize'));
        $cacheInfo->setData('current_page', $searchCriteria->getData('currentPage'));

        if ($loadingData->getData('is_full_loading') === true) {
            $cacheInfo->setData('is_over', true);
        }

        $cacheInfo->save();
    }

}
