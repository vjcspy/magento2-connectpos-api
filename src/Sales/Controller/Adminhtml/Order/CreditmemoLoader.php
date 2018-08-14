<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 02/01/2017
 * Time: 21:51
 */

namespace SM\Sales\Controller\Adminhtml\Order;


class CreditmemoLoader extends \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader {

    /**
     * Check if creditmeno can be created for order
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return bool
     */
    protected function _canCreditmemo($order) {
        /**
         * Check order existing
         */
        if (!$order->getId()) {
            throw new \Exception(__('The order no longer exists.'));
        }

        /**
         * Check creditmemo create availability
         */
        if (!$order->canCreditmemo()) {
            throw new \Exception(__('We can\'t create credit memo for the order.'));
        }

        return true;
    }
}