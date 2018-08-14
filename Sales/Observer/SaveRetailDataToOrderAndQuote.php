<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 06/12/2016
 * Time: 15:26
 */

namespace SM\Sales\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SaveOutletIdToOrderAndQuote
 *
 * @package SM\Sales\Observer
 */
class SaveRetailDataToOrderAndQuote implements ObserverInterface {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * SaveOutletIdToOrderAndQuote constructor.
     *
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $order = $observer->getData('order');
        $quote = $observer->getData('quote');

        $outletId = $this->registry->registry('outlet_id');
        if (!!$outletId) {
            $quote->setData('outlet_id', $outletId);
            $order->setData('outlet_id', $outletId);
        }
        $register_id = $this->registry->registry('register_id');
        if (!!$register_id) {
            $quote->setData('register_id', $register_id);
            $order->setData('register_id', $register_id);
        }

        $retailId = $this->registry->registry('retail_id');
        if (!!$retailId) {
            $quote->setData('retail_id', $retailId);
            $order->setData('retail_id', $retailId);
        }

        $retailNote = $this->registry->registry('retail_note');
        if (!!$retailNote) {
            $quote->setData('retail_note', $retailNote);
            $order->setData('retail_note', $retailNote);
        }

        $retailStatus = $this->registry->registry('retail_status');
        if (!!$outletId) {
            $quote->setData('retail_status', $retailStatus);
            $order->setData('retail_status', $retailStatus);
        }

        $retailHasShipment = $this->registry->registry('retail_has_shipment');
        if (!!$outletId) {
            $quote->setData('retail_has_shipment', $retailHasShipment);
            $order->setData('retail_has_shipment', $retailHasShipment);
        }

        $userId = $this->registry->registry('user_id');
        if (!!$outletId) {
            $quote->setData('user_id', $userId);
            $order->setData('user_id', $userId);
        }
        $sellerIds = $this->registry->registry('sm_seller_ids');
        if (!!$sellerIds) {
            $quote->setData('sm_seller_ids', $sellerIds);
            $order->setData('sm_seller_ids', $sellerIds);
        }

        $retailExchange = $this->registry->registry('is_exchange');
        if (!!$retailExchange) {
            $quote->setData('is_exchange', $retailExchange);
            $order->setData('is_exchange', $retailExchange);
        }
    }
}