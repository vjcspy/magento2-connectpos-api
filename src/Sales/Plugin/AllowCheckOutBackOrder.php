<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 31/12/2016
 * Time: 22:26
 */

namespace SM\Sales\Plugin;


use Magento\Backend\Helper\Dashboard\Order;
use SM\Sales\Repositories\OrderManagement;

class AllowCheckOutBackOrder {

    public function afterCheckQty(
        \Magento\CatalogInventory\Model\StockState $subject,
        $result
    ) {
        return OrderManagement::$ALLOW_BACK_ORDER == true ? OrderManagement::$ALLOW_BACK_ORDER : $result;
    }
}