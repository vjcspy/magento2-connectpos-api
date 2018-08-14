<?php

namespace SM\Integrate\Observer\Giftcard\Aw;

use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\ProductFactory;
class HandleAfterCollectTotalRefundToGC implements \Magento\Framework\Event\ObserverInterface {

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
        \Magento\Framework\Registry $registry,
        ProductFactory $productFactory
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
      $collection = $observer->getData('collection');
      $isUsingRefundToGCProduct = $this->registry->registry(\SM\Sales\Repositories\OrderManagement::USING_REFUND_TO_GIFT_CARD);
        if ($collection instanceof \Magento\SalesRule\Model\ResourceModel\Rule\Collection) {
            if($isUsingRefundToGCProduct){
                $collection->clear();
            }
        }
    }

}