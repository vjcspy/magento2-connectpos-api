<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/10/17
 * Time: 11:46 AM
 */

namespace SM\Integrate\Warehouse\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use SM\Integrate\Model\WarehouseIntegrateManagement;

class FilterProduct implements ObserverInterface {


    /**
     * @var \SM\Integrate\Model\WarehouseIntegrateManagement
     */
    private $warehouseIntegrateManagement;

    /**
     * FilterProduct constructor
     *
     * @param \SM\Integrate\Model\WarehouseIntegrateManagement $warehouseIntegrateManagement
     */
    public function __construct(
        WarehouseIntegrateManagement $warehouseIntegrateManagement
    ) {
        $this->warehouseIntegrateManagement = $warehouseIntegrateManagement;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $collection  = $observer->getData('collection');
        $warehouseId = $observer->getData('warehouse_id');

        $this->warehouseIntegrateManagement->getCurrentIntegrateModel()->filterProductCollectionByWarehouse($collection, $warehouseId, null);
    }
}
