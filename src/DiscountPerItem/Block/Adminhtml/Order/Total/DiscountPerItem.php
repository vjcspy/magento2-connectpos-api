<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 06/12/2016
 * Time: 15:07
 */

namespace SM\DiscountPerItem\Block\Adminhtml\Order\Total;


use Magento\Framework\DataObject;

class DiscountPerItem extends \Magento\Sales\Block\Adminhtml\Order\Totals {

    public function initTotals() {
        $totalsBlock = $this->getParentBlock();
        $order       = $totalsBlock->getOrder();
        if ($order->getData('discount_per_item') < 0) {
            $totalsBlock->addTotal(
                new DataObject(
                    [
                        'code'        => 'discount_per_item',
                        'label'       => __('Total Discount PerItem'),
                        'value'       => -floatval($order->getData('discount_per_item')),
                        'is_formated' => false
                    ]),
                'subtotal');
        }
    }
}
