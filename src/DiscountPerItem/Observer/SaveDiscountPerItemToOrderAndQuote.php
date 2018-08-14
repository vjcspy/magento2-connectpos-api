<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 06/12/2016
 * Time: 15:26
 */

namespace SM\DiscountPerItem\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveDiscountPerItemToOrderAndQuote implements ObserverInterface {

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $order = $observer->getData('order');
        $quote = $observer->getData('quote');
        if ($quote->getData('is_virtual') == 1) {
            $discount     = $quote->getData('retail_discount_per_item');
            $baseDiscount = $quote->getData('base_retail_discount_per_item');
        }else {
            $discount     = $quote->getShippingAddress()->getData('retail_discount_per_item_amount');
            $baseDiscount = $quote->getShippingAddress()->getData('base_retail_discount_per_item_amount');
        }

        if ($discount) {
            $quote->setData('discount_per_item', $discount);
            $quote->setData('base_discount_per_item', $baseDiscount);
            $order->setData('discount_per_item', $discount);
            $order->setData('base_discount_per_item', $baseDiscount);
        }
    }
}