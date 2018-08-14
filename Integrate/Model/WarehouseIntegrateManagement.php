<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/10/17
 * Time: 11:34 AM
 */

namespace SM\Integrate\Model;


use SM\Core\Model\DataObject;
use SM\XRetail\Repositories\Contract\ServiceAbstract;
use Magento\Catalog\Model\ProductFactory;
use SM\Core\Api\Data\XProduct;
use SM\Product\Repositories\ProductManagement\ProductStock;

class WarehouseIntegrateManagement extends ServiceAbstract {

    private static $_WAREHOUSE_ID = null;

    /**
     * @var array
     */
    static $LIST_WH_INTEGRATE
        = [
            'ahead_works' => [
                [
                    "version" => "~1.0.0",
                    "class"   => "SM\\Integrate\\RewardPoint\\AheadWorks100"
                ]
            ],
            'bms'         => [
                [
                    "version" => "~0.0.15",
                    "class"   => "SM\\Integrate\\Warehouse\\BootMyShop0015"
                ]
            ],
            'mage_store'  => [
                [
                    'version' => "~1.1.1",
                    "class"   => "SM\\Integrate\\Warehouse\\Magestore111"
                ]
            ],
        ];
    /**
     * @var \SM\Integrate\Warehouse\Contract\WarehouseIntegrateInterface
     */
    private $_currentIntegrateModel;
    /**
     * @var \SM\Integrate\Model\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \SM\Integrate\Helper\Data
     */
    private $integrateData;
    /**
     * @var \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory
     */
    private $outletCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\Product\ProductFactory
     */
    private $productFactory;
    /**
     * @var \SM\Product\Repositories\ProductManagement\ProductStock
     */
    private $productStock;
    /**
     * WarehouseIntegrateManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface                  $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                            $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface               $storeManager
     * @param \Magento\Framework\ObjectManagerInterface                $objectManager
     * @param \SM\Integrate\Helper\Data                                $integrateData
     * @param \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory $outletCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \SM\Integrate\Helper\Data $integrateData,
        \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory $outletCollectionFactory,
        ProductFactory $productFactory,
        ProductStock $productStock
    ) {
        $this->integrateData           = $integrateData;
        $this->objectManager           = $objectManager;
        $this->outletCollectionFactory = $outletCollectionFactory;
        $this->productFactory          = $productFactory;
        $this->productStock            = $productStock;

        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return null
     */
    public static function getWarehouseId() {
        return self::$_WAREHOUSE_ID;
    }

    /**
     * @param null $WAREHOUSE_ID
     */
    public static function setWarehouseId($WAREHOUSE_ID) {
        self::$_WAREHOUSE_ID = $WAREHOUSE_ID;
    }

    /**
     * @return \SM\Integrate\Warehouse\Contract\WarehouseIntegrateInterface
     */
    public function getCurrentIntegrateModel() {
        if (is_null($this->_currentIntegrateModel)) {
            // FIXME: do something to get current integrate class
            $class = self::$LIST_WH_INTEGRATE['bms'][0]['class'];

            $this->_currentIntegrateModel = $this->objectManager->create($class);
        }

        return $this->_currentIntegrateModel;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getList() {
        return $this->getCurrentIntegrateModel()->loadWarehouseData($this->getSearchCriteria())->getOutput();
    }

    /**
     * @param                               $product
     * @param                               $warehouseId
     * @param \Magento\Framework\DataObject $item
     *
     * @return mixed
     */
    public function getStockItem($product, $warehouseId, $item = null) {
        return $this->getCurrentIntegrateModel()->getStockItem($product, $warehouseId, $item);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getWarehouseItem() {
        $items      = [];
        $collection = $this->getOutletCollection();
        foreach ($collection as $outlet) {
            $warehouse = $this->getCurrentIntegrateModel()->getWarehouseCollection(
                new DataObject(
                    [
                        'entity_id' => $outlet->getData('warehouse_id')
                    ]))->getFirstItem();

            if ($warehouse->getData("w_id")) {
                $_data = [
                    "warehouse_name"  => $warehouse->getData("w_name"),
                    "warehouse_id"    => $warehouse->getData("w_id"),
                    "outlet_id"       => $outlet->getId(),
                    "outlet_name"     => $outlet->getName(),
                    "warehouse_stock" => $this->getCurrentIntegrateModel()->getWarehouseStockItem(
                        $this->getSearchCriteria()->getData("entity_id"),
                        $warehouse->getData("w_id"))
                ];

                array_push($items, $_data);
            }
            else {
                continue;
            }
        }

        return $this->getSearchResult()
                    ->setItems($items)
                    ->setTotalCount($collection->getSize())
                    ->setLastPageNumber($collection->getLastPageNumber())
                    ->getOutput();
    }

    public function getWarehouseSpecifiedItem() {
        $items      = [];
        $searchCriteria = $this->getSearchCriteria();
        $warehouseId = is_null($searchCriteria->getData('warehouse_id'))
            ? $searchCriteria->getData('warehouseId')
            : $searchCriteria->getData(
                'warehouse_id');

        $storeId = is_null($searchCriteria->getData('store_id'))
            ? $searchCriteria->getData('storeId')
            : $searchCriteria->getData(
                'store_id');

        $store = $this->storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();

        $productId = $this->getSearchCriteria()->getData("entity_id");

        $product = $this->getProductModel()->load($productId);
        $_children = $product->getTypeInstance()->getUsedProducts($product);
        foreach ($_children as $child) {
            if (in_array($websiteId, $child->getWebsiteIds())) {
                $items[] = $this->processXProduct($child, $storeId, $warehouseId, $child);
            }
        }
        return $this->getSearchResult()
                    ->setSearchCriteria($searchCriteria)
                    ->setItems($items)
                    ->setTotalCount(count($items))
                    ->getOutput();
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

        $xProduct->setData('store_id', $storeId);

        // get stock_items
        if (!$this->integrateData->isIntegrateWH() || !$warehouseId) {
            $xProduct->setData(
                'stock_items',
                $this->getProductStock()->getStock($product, 0));
        }
        else {
            $xProduct->setData(
                'stock_items',
                $this->getStockSpecifiedItem($product, $warehouseId, $item));
        }

        return $xProduct;
    }

    public function getStockSpecifiedItem($product, $warehouseId, $item) {
        $defaultStock = $this->productStock->getStock($product, 0);

        $warehouseStock = $this->getCurrentIntegrateModel()->getWarehouseStockItem(
            $product->getEntityId(),
            $warehouseId);
        if ($warehouseStock) {
            $defaultStock['qty'] = $warehouseStock['available_quantity'];
        } else {
            $defaultStock['qty'] = 0;
        }

        return $defaultStock;
    }

    /**
     * @return \SM\XRetail\Model\ResourceModel\Outlet\Collection
     */
    protected function getOutletCollection() {
        return $this->outletCollectionFactory->create();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductModel() {
        return $this->productFactory->create();
    }

    /**
     * @return \SM\Product\Repositories\ProductManagement\ProductStock
     */
    public function getProductStock() {
        return $this->productStock;
    }
}
