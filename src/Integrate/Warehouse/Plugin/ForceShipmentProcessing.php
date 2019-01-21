<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 3/26/18
 * Time: 16:57
 */

namespace SM\Integrate\Warehouse\Plugin;


use SM\Integrate\Model\WarehouseIntegrateManagement;
use SM\Sales\Repositories\OrderManagement;

class ForceShipmentProcessing {

    public function aroundExecute(
        \BoostMyShop\OrderPreparation\Observer\SalesOrderShipmentSaveAfter $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        if ((OrderManagement::$FROM_API && WarehouseIntegrateManagement::getWarehouseId()) || \SM\Sales\Repositories\ShipmentManagement::$FROM_API) {
            return;
        }
        else {
            return $proceed($observer);
        }
    }
}
