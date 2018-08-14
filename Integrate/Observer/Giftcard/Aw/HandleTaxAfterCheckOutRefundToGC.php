<?php

namespace SM\Integrate\Observer\Giftcard\Aw;

use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\ProductFactory;
class HandleTaxAfterCheckOutRefundToGC implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var SM\Integrate\Helper\Data
     */
    private $integrateHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \SM\Integrate\Helper\Data $integrateHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->integrateHelper = $integrateHelper;
        $this->registry      = $registry;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer) {
        $request = $observer->getData('request');
        $isUsingRefundToGCProduct = $this->registry->registry(\SM\Sales\Repositories\OrderManagement::USING_REFUND_TO_GIFT_CARD);
        // fake customer tax class id đối với order có mua refund to giftcard
        if($isUsingRefundToGCProduct && $request->getData('customer_class_id')){
            $request->setData('customer_class_id','1527499479');
        }
    }

}