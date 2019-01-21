<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/10/17
 * Time: 11:36 AM
 */

namespace SM\Integrate\Warehouse;


use Magento\Framework\ObjectManagerInterface;
use SM\Integrate\Warehouse\Contract\AbstractWarehouseIntegrate;
use SM\Integrate\Warehouse\Contract\WarehouseIntegrateInterface;
use SM\XRetail\Helper\DataConfig;

class Magestore111 extends AbstractWarehouseIntegrate implements WarehouseIntegrateInterface {

    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\Location\MappingManagementInterface
     */
    private $mappingManagement;
    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface
     */
    private $warehouseManagement;
    /**
     * @var \SM\Product\Repositories\ProductManagement\ProductStock
     */
    private $productStock;

    /**
     * Magestore111 constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface               $objectManager
     * @param \SM\Integrate\Helper\Data                               $integrateData
     * @param \SM\Product\Repositories\ProductManagement\ProductStock $productStock
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \SM\Integrate\Helper\Data $integrateData,
        \SM\Product\Repositories\ProductManagement\ProductStock $productStock
    ) {
        $this->productStock = $productStock;
        parent::__construct($objectManager, $integrateData);
    }

    /**
     * @param $warehouseId
     *
     * @return array
     */
    public function getListProductByWarehouse($warehouseId) {
        $result     = $this->warehouseManagement->getListProduct($warehouseId);
        $productIds = [];
        if ($result->getSize()) {
            foreach ($result as $item) {
                $productIds[$item->getProductId()] = $item->getProductId();
            }
        }

        return $productIds;
    }

    /**
     * @param $collection
     * @param $warehouseId
     * @param $searchCriteria
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function filterProductCollectionByWarehouse($collection, $warehouseId, $searchCriteria) {
        return $collection->addFieldToFilter('entity_id', ['in' => $this->getListProductByWarehouse($warehouseId)]);
    }


    /**
     * @return \Magestore\InventorySuccess\Api\Warehouse\Location\MappingManagementInterface
     */
    protected function getMappingManagement() {
        if (is_null($this->mappingManagement)) {
            $this->mappingManagement = $this->objectManager->create('Magestore\InventorySuccess\Api\Warehouse\Location\MappingManagementInterface');
        }

        return $this->mappingManagement;
    }

    /**
     * @return \Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface
     */
    protected function getWarehouseManagement() {
        if (is_null($this->warehouseManagement)) {
            $this->warehouseManagement = $this->objectManager->create('Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface');
        }

        return $this->warehouseManagement;
    }

    /**
     * @param $searchCriteria
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getWarehouseCollection($searchCriteria) {
        /** @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection */
        $collection = $this->objectManager->create('Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection');

        $collection->setCurPage(is_nan($searchCriteria->getData('currentPage')) ? 1 : $searchCriteria->getData('currentPage'));
        $collection->setPageSize(
            is_nan($searchCriteria->getData('pageSize')) ? DataConfig::PAGE_SIZE_LOAD_DATA : $searchCriteria->getData('pageSize')
        );

        return $collection;
    }

    public function getStockItem($product, $warehouseId, $item = null) {
        return $this->productStock->getStock($product, $warehouseId);
    }

    /**
     * @param $searchCriteria
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getWarehouseItemCollection($searchCriteria) {
        // TODO: Implement getWarehouseItemCollection() method.
    }

    /**
     * @param int $productId
     * @param int $warehouseId
     *
     * @return array
     */
    public function getWarehouseStockItem($productId, $warehouseId) {
        // TODO: Implement getWarehouseStockItem() method.
    }
}
