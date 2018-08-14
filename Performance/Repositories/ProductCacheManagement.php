<?php

namespace SM\Performance\Repositories;

use Magento\Framework\ObjectManagerInterface;
use SM\XRetail\Repositories\Contract\ServiceAbstract;
use Magento\Framework\DataObject;

class ProductCacheManagement extends ServiceAbstract {

    /**
     * @var \SM\Performance\Model\ProductCacheInstanceFactory
     */
    protected $productCacheInstanceFactory;

    /**
     * @var \SM\Performance\Model\ResourceModel\ProductCacheInstance\CollectionFactory
     */
    protected $productCacheInstanceCollectionFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \SM\Performance\Helper\CacheKeeper
     */
    private $cacheKeeper;

    /**
     * ProductCacheManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface                                    $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                                              $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface                                 $storeManager
     * @param \Magento\Framework\ObjectManagerInterface                                  $objectManager
     * @param \SM\Performance\Model\ProductCacheInstanceFactory                          $productCacheInstanceFactory
     * @param \SM\Performance\Model\ResourceModel\ProductCacheInstance\CollectionFactory $productCacheInstanceCollectionFactory
     * @param \SM\Performance\Helper\CacheKeeper                                         $cacheKeeper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        \SM\Performance\Model\ProductCacheInstanceFactory $productCacheInstanceFactory,
        \SM\Performance\Model\ResourceModel\ProductCacheInstance\CollectionFactory $productCacheInstanceCollectionFactory,
        \SM\Performance\Helper\CacheKeeper $cacheKeeper
    ) {
        $this->cacheKeeper                           = $cacheKeeper;
        $this->objectManager                         = $objectManager;
        $this->productCacheInstanceFactory           = $productCacheInstanceFactory;
        $this->productCacheInstanceCollectionFactory = $productCacheInstanceCollectionFactory;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return array
     */
    public function getListProductCache() {
        $searchCriteria = $this->getSearchCriteria();

        return $this->getProductCache($searchCriteria);
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return array
     */
    public function getProductCache(DataObject $searchCriteria) {
        $collection = $this->productCacheInstanceCollectionFactory->create();
        $items      = [];
        if (1 < $searchCriteria->getData('currentPage')) {
        }
        else {
            foreach ($collection as $productCache) {
                $productCacheData = $productCache->getData();
                $items[]          = $productCacheData;
            }
        }

        return $this->getSearchResult()
                    ->setSearchCriteria($searchCriteria)
                    ->setItems($items)
                    ->getOutput();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function removeProductCache() {
        $id = $this->getRequest()->getParam('id');
        if (is_null($id)) {
            throw new \Exception("Must define required data");
        }
        $productCacheItem = $this->productCacheInstanceFactory->create()->load($id);
        if (!$productCacheItem->getId()) {
            throw new \Exception("Can not find product cache data");
        }
        else {
            $this->cacheKeeper->dropCacheTable($productCacheItem->getData('store_id'), $productCacheItem->getData('warehouse_id'));
            $productCacheItem->delete();
        }

        return ['messages' => 'Product data has been deleted'];
    }
}
