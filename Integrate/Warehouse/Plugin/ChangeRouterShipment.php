<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 3/26/18
 * Time: 15:59
 */

namespace SM\Integrate\Warehouse\Plugin;


use SM\Integrate\Model\WarehouseIntegrateManagement;
use SM\Sales\Repositories\OrderManagement;

class ChangeRouterShipment {

    public function afterGetWarehouseIdForOrderItem(
        \BoostMyShop\AdvancedStock\Model\Router $subject,
        $result
    ) {
        if (OrderManagement::$FROM_API && WarehouseIntegrateManagement::getWarehouseId()) {
            return WarehouseIntegrateManagement::getWarehouseId();
        }

        return $result;
    }
}
