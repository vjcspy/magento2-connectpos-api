<?php
/**
 * Created by mr.vjcspy@gmail.com/khoild@smartosc.com.
 * Date: 2/4/16
 * Time: 11:27 AM
 */

namespace SM\Product\Repositories;


use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use SM\Core\Api\Data\XProduct;
use SM\Core\Api\Data\XProductFactory;
use SM\Core\Model\DataObject;
use SM\Integrate\Model\WarehouseIntegrateManagement;
use SM\Performance\Helper\CacheKeeper;
use SM\Performance\Model\Cache\Type\RetailProduct;
use SM\Product\Helper\ProductImageHelper;
use SM\Product\Repositories\ProductManagement\ProductAttribute;
use SM\Product\Repositories\ProductManagement\ProductMediaGalleryImages;
use SM\Product\Repositories\ProductManagement\ProductOptions;
use SM\Product\Repositories\ProductManagement\ProductPrice;
use SM\Product\Repositories\ProductManagement\ProductStock;
use SM\XRetail\Helper\DataConfig;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

/**
 * Class ProductManagement
 *
 * @package SM\XRetail\Model\ResourceModel
 */
class ProductManagement extends ServiceAbstract {

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productFactory;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var CustomSalesHelper
     */
    protected $_customSalesHelper;
    /**
     * @var \SM\Product\Repositories\ProductManagement\ProductMediaGalleryImages
     */
    protected $productMediaGalleryImages;
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProduct;
    /**
     * @var \SM\Product\Repositories\ProductManagement\ProductOptions
     */
    private $productOptions;
    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    private $productMediaConfig;
    /**
     * @var \SM\Product\Repositories\ProductManagement\ProductAttribute
     */
    private $productAttribute;
    /**
     * @var \SM\Product\Repositories\ProductManagement\ProductStock
     */
    private $productStock;
    /**
     * @var \SM\Product\Repositories\ProductManagement\ProductPrice
     */
    private $productPrice;
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $cache;

    /**
     * @var string
     */
    static $CACHE_TAG = "xProduct";
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManagement;
    /**
     * @var \SM\Integrate\Model\WarehouseIntegrateManagement
     */
    private $warehouseIntegrateManagement;
    /**
     * @var \SM\Integrate\Helper\Data
     */
    private $integrateData;
    /**
     * @var \SM\Product\Helper\ProductHelper
     */
    private $productHelper;
    /**
     * @var \SM\Product\Helper\ProductImageHelper
     */
    private $productImageHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * ProductManagement constructor.
     *
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param RequestInterface $requestInterface
     * @param DataConfig $dataConfig
     * @param StoreManagerInterface $storeManager
     * @param ProductFactory $productFactory
     * @param CollectionFactory $collectionFactory
     * @param \SM\Product\Repositories\ProductManagement\ProductOptions $productOptions
     * @param \Magento\Catalog\Model\Product\Media\Config $productMediaConfig
     * @param \SM\Product\Repositories\ProductManagement\ProductAttribute $productAttribute
     * @param \SM\Product\Repositories\ProductManagement\ProductStock $productStock
     * @param \SM\Product\Repositories\ProductManagement\ProductPrice $productPrice
     * @param \SM\Product\Repositories\ProductManagement\ProductMediaGalleryImages $productMediaGalleryImages
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \SM\CustomSale\Helper\Data $customSaleHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManagement
     *
     * @param \SM\Integrate\Helper\Data $integrateData
     * @param WarehouseIntegrateManagement $warehouseIntegrateManagement
     * @param \SM\Product\Helper\ProductHelper $productHelper
     * @param ProductImageHelper $productImageHelper
     * @param \Magento\Framework\Registry $registry
     * @internal param \SM\Product\Repositories\CustomSalesHelper $customSalesHelper
     */
    public function __construct(
        \Magento\Framework\Cache\FrontendInterface $cache,
        RequestInterface $requestInterface,
        DataConfig $dataConfig,
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        CollectionFactory $collectionFactory,
        ProductOptions $productOptions,
        \Magento\Catalog\Model\Product\Media\Config $productMediaConfig,
        ProductAttribute $productAttribute,
        ProductStock $productStock,
        ProductPrice $productPrice,
        ProductMediaGalleryImages $productMediaGalleryImages,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \SM\CustomSale\Helper\Data $customSaleHelper,
        \Magento\Framework\Event\ManagerInterface $eventManagement,
        \SM\Integrate\Helper\Data $integrateData,
        WarehouseIntegrateManagement $warehouseIntegrateManagement,
        \SM\Product\Helper\ProductHelper $productHelper,
        ProductImageHelper $productImageHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->cache                        = $cache;
        $this->catalogProduct               = $catalogProduct;
        $this->productPrice                 = $productPrice;
        $this->productAttribute             = $productAttribute;
        $this->productFactory               = $productFactory;
        $this->collectionFactory            = $collectionFactory;
        $this->_customSalesHelper           = $customSaleHelper;
        $this->productOptions               = $productOptions;
        $this->productMediaConfig           = $productMediaConfig;
        $this->productStock                 = $productStock;
        $this->productMediaGalleryImages    = $productMediaGalleryImages;
        $this->eventManagement              = $eventManagement;
        $this->warehouseIntegrateManagement = $warehouseIntegrateManagement;
        $this->integrateData                = $integrateData;
        $this->productHelper                = $productHelper;
        $this->productImageHelper           = $productImageHelper;
        $this->registry                     = $registry;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getProductData() {
        return $this->loadXProducts($this->getSearchCriteria())->getOutput();
    }

    /**
     * @param null $searchCriteria
     *
     * @return \SM\Core\Api\SearchResult
     * @throws \Exception
     */
    public function loadXProducts($searchCriteria = null) {
        if (is_null($searchCriteria) || !$searchCriteria) {
            $searchCriteria = $this->getSearchCriteria();
        }

        $searchCriteria->setData('currentPage', is_nan($searchCriteria->getData('currentPage')) ? 1 : $searchCriteria->getData('currentPage'));
        $searchCriteria->setData(
            'pageSize',
            is_nan($searchCriteria->getData('pageSize')) ? DataConfig::PAGE_SIZE_LOAD_PRODUCT : $searchCriteria->getData('pageSize'));

        $collection = $this->getProductCollection($searchCriteria);
        $storeId    = $this->getStoreManager()->getStore()->getId();

        WarehouseIntegrateManagement::setWarehouseId(
            is_null($searchCriteria->getData('warehouse_id'))
                ? $searchCriteria->getData('warehouseId')
                : $searchCriteria->getData(
                'warehouse_id'));

        $items = [];

        $loadingData = new DataObject(
            [
                'collection'      => $collection,
                'search_criteria' => $searchCriteria,
                'items'           => $items
            ]);
        $this->eventManagement->dispatch(
            'before_load_x_product',
            ['loading_data' => $loadingData]
        );

        if ($loadingData->getData(CacheKeeper::$IS_PULL_FROM_CACHE) === true && $searchCriteria->getData('searchOnline') != 1) {
            $items = $loadingData->getData('items');
            $this->getSearchResult()->setCacheTime($loadingData->getData('cache_time'));
        }
        else {

            if ($collection->getLastPageNumber() < $searchCriteria->getData('currentPage')) {
                $loadingData->setData('is_full_loading', true);
            }
            else {
                // Skip salesable check when collect child product
                $this->catalogProduct->setSkipSaleableCheck(true);

                // Fix can't get product because magento add plugin before load to filter status product. Unknown why table "cataloginventory_stock_status" hasn't status of product(maybe bms_warehouse cause this)
                $collection->setFlag("has_stock_status_filter", true);

                foreach ($collection as $item) {
                    try {
                        $product = $this->getProductModel()->load($item->getId());

                        $items[] = $this->processXProduct($product, $storeId, WarehouseIntegrateManagement::getWarehouseId(), $item);
                    }
                    catch (\Exception $e) {
                    }
                }
            }
            $loadingData->setData('items', $items);

            $this->eventManagement->dispatch(
                'after_load_x_product',
                [
                    'loading_data' => $loadingData
                ]);
        }

        return $this->getSearchResult()
                    ->setSearchCriteria($searchCriteria)
                    ->setIsLoadFromCache($loadingData->getData(CacheKeeper::$IS_PULL_FROM_CACHE))
                    ->setItems($items)
                    ->setTotalCount($collection->getSize())
                    ->setLastPageNumber($collection->getLastPageNumber());
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param                                $storeId
     * @param                                $warehouseId
     * @param DataObject                     $item
     *
     * @return \SM\Core\Api\Data\XProduct
     * @throws \Exception
     */
    protected function processXProduct(\Magento\Catalog\Model\Product $product, $storeId, $warehouseId, $item = null) {
        /** @var \SM\Core\Api\Data\XProduct $xProduct */
        $xProduct = new XProduct();
        $xProduct->addData($product->getData());

        $xProduct->setData('tier_prices', $this->getProductPrice()->getExistingPrices($product, 'tier_price', true));

        $xProduct->setData('store_id', $storeId);

        $xProduct->setData('origin_image', $this->productImageHelper->getImageUrl($product));

        $xProduct->setData('media_gallery', $this->productMediaGalleryImages->getMediaGalleryImages($product));

        $xProduct->setData('custom_attributes', $this->getProductAttribute()->getCustomAttributes($product));

        // get options
        if (($this->getSearchCriteria()->getData('isFindProduct') == 1 && $this->getSearchCriteria()->getData('isViewDetail') == true) || $this->getSearchCriteria()->getData('searchOnline') != 1) {
            // get options
            $xProduct->setData('x_options', $this->getProductOptions()->getOptions($product));
        }

        $xProduct->setData('customizable_options', $this->getProductOptions()->getCustomizableOptions($product));

        // get stock_items
        if (!$this->integrateData->isIntegrateWH() || !$warehouseId) {
            $xProduct->setData(
                'stock_items',
                $this->getProductStock()->getStock($product, 0));
        }
        else {
            $xProduct->setData(
                'stock_items',
                $this->warehouseIntegrateManagement->getStockItem($product, $warehouseId, $item));
        }

        $xProduct->setData(
            'addition_search_fields',
            array_reduce(
                $this->productHelper->getProductAdditionAttribute(),
                function ($result, $field) use ($product) {
                    return !!$field && is_string($field) ? $result . ' ' . json_encode($product->getData($field)) : $result;
                },
                ''));

        return $xProduct;
    }

    /**
     * @param $storeId
     * @param $warehouseId
     *
     * @return \SM\Core\Api\Data\XProduct
     * @throws \Exception
     */
    public function getCustomSaleData($storeId, $warehouseId) {
        $product = $this->_customSalesHelper->getCustomSaleProduct();

        return $this->processXProduct($product, $storeId, $warehouseId);
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Exception
     */
    public function getProductCollection(\Magento\Framework\DataObject $searchCriteria) {
        $this->registry->register('disableFlatProduct', true);
        $storeId = $this->getSearchCriteria()->getData('storeId');
        if (is_null($storeId)) {
            throw new \Exception(__('Must have param storeId'));
        }
        else {
            $this->getStoreManager()->setCurrentStore($storeId);
        }
        $websiteId = $this->getStoreManager()->getWebsite()->getId();
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        if (!$collection->isEnabledFlat()) {
            $collection->addAttributeToSelect('*');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
            $collection->getSelect()->join(
                ['cataloginventory_stock_item' => $collection->getTable('cataloginventory_stock_item')],
                'cataloginventory_stock_item.product_id=e.entity_id',
                ['stock_status' => 'cataloginventory_stock_item.is_in_stock']
            )->where("cataloginventory_stock_item.website_id=0");
            //$collection->joinTable(
            //    $collection->getTable('cataloginventory_stock_item'),
            //    'product_id=entity_id',
            //    ['stock_status' => 'is_in_stock'])->addAttributeToSelect('stock_status');

            $collection->addStoreFilter($storeId);
        }
        $collection->setCurPage($searchCriteria->getData('currentPage'));
        $collection->setPageSize($searchCriteria->getData('pageSize'));

        //if ($searchCriteria->getData('status')) {
        //    // 1: Enable/ 2: Disable
        //    $collection->addAttributeToFilter('status', ['in' => $searchCriteria->getData('status')]);
        //}
        //
        //if ($searchCriteria->getData('visibility')) {
        //    // 1: Not Visible Individually / 2: Catalog / 3: Search / 4: Catalog, Search
        //    $collection->addAttributeToFilter('visibility', ['in' => $searchCriteria->getData('visibility')]);
        //}

        if ($searchCriteria->getData('typeId')) {
            $collection->addAttributeToFilter('type_id', ['in' => $searchCriteria->getData('typeId')]);
        }

        if ($searchCriteria->getData('productIds')) {
            $collection->addFieldToFilter('entity_id', ['in' => $searchCriteria->getData('productIds')]);
        }
        if ($searchCriteria->getData('entity_id') || $searchCriteria->getData('entityId')) {
            $ids = is_null($searchCriteria->getData('entity_id')) ? $searchCriteria->getData('entityId') : $searchCriteria->getData('entity_id');
            $collection->addFieldToFilter('entity_id', ['in' => explode(",", $ids)]);
        }

        //$collection->addFieldToFilter('entity_id', ['lt' => 200]);

        if ($this->integrateData->isIntegrateWH() && ($searchCriteria->getData('warehouse_id') || $searchCriteria->getData('warehouseId'))) {
            $id = is_null($searchCriteria->getData('warehouse_id'))
                ? $searchCriteria->getData('warehouseId')
                : $searchCriteria->getData(
                    'warehouse_id');
            $this->eventManagement->dispatch(
                "pos_integrate_warehouse_filter_product",
                ['collection' => $collection, 'warehouse_id' => $id]);
        }


        if ($searchCriteria->getData('searchOnline') == 1) {
         $collection = $this->searchProductOnlineCollection($searchCriteria, $collection);
        }
        $this->registry->unregister('disableFlatProduct');
        return $collection;
    }

    public function searchProductOnlineCollection($searchCriteria, $collection){
        if ($searchCriteria->getData('isFindProduct') == 1) {
            if ($searchCriteria->getData('isViewDetail') && $searchCriteria->getData('isViewDetail') == true) {
                $product      = $this->getProductModel()->load($searchCriteria->getData('searchValue'));
                $_configChild = [$product->getData('entity_id')];
                if ($product->getTypeId() != 'simple') {
                    $listChild = $product->getTypeInstance()->getChildrenIds($product->getId());

                    foreach ($listChild as $child) {
                        $_configChild = array_merge($_configChild, $child);
                    }
                }
                $collection->addFieldToFilter('entity_id', ['in' => $_configChild]);
            }
            else {
                $collection->addFieldToFilter('entity_id', ['in' => $searchCriteria->getData('searchValue')]);
            }
        }else {
            if ($searchCriteria->getData('showOutStock') != 1) {
                $collection->getSelect()->where('cataloginventory_stock_item.is_in_stock = 1');
            }
            $searchValue = $searchCriteria->getData('searchValue');
            $searchValue = str_replace(',', ' ', $searchValue);
            //$searchField = $searchCriteria->getData('searchFields');
            $searchField = $this->productHelper->getSearchOnlineAttribute(explode(",", $searchCriteria->getData('searchFields')));
            if ($searchValue == 'null' || $searchValue == ' ' || $searchValue == '') {
                $collection->addFieldToFilter('entity_id', null);
            }
            foreach (explode(" ", $searchValue) as $value) {
                $_fieldFilters = [];
                foreach ($searchField as $field) {

                    if ($field === 'id') {
                        $_fieldFilters[] = ['attribute' => 'entity_id', 'like' => '%' . $value . '%'];
                    }
                    else {
                        $_fieldFilters[] = ['attribute' => $field, 'like' => '%' . $value . '%'];
                    }
                }
                //$collection->addFieldToFilter($_fieldFilters, $_valueFilters);
                $collection->addAttributeToFilter($_fieldFilters ,null, 'left');
            }
            if ($searchCriteria->getData('sortValue') && $searchCriteria->getData('sortType')) {
                $collection->addAttributeToSort($searchCriteria->getData('sortValue'), $searchCriteria->getData('sortType'));
            }
        }
        return $collection;
    }

    /**
     * @return \SM\Product\Repositories\ProductManagement\ProductOptions
     */
    public function getProductOptions() {
        return $this->productOptions;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Media\Config
     */
    public function getProductMediaConfig() {
        return $this->productMediaConfig;
    }

    /**
     * @return \SM\Product\Repositories\ProductManagement\ProductAttribute
     */
    public function getProductAttribute() {
        return $this->productAttribute;
    }

    /**
     * @return  \SM\CustomSale\Helper\Data
     */
    public function getCustomSalesHelper() {
        return $this->_customSalesHelper;
    }

    /**
     * @return \SM\Product\Repositories\ProductManagement\ProductStock
     */
    public function getProductStock() {
        return $this->productStock;
    }

    /**
     * @return \SM\Product\Repositories\ProductManagement\ProductPrice
     */
    public function getProductPrice() {
        return $this->productPrice;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductModel() {
        return $this->productFactory->create();
    }
}
