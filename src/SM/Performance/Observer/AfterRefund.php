<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 13/03/2017
 * Time: 14:31
 */

namespace SM\Performance\Observer;


use Magento\Sales\Api\Data\CreditmemoInterface;
use SM\Performance\Helper\RealtimeManager;

class AfterRefund implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \SM\Performance\Helper\RealtimeManager
     */
    private $realtimeManager;

    /**
     * AfterCheckout constructor.
     *
     * @param \SM\Performance\Helper\RealtimeManager $realtimeManager
     */
    public function __construct(
        \SM\Performance\Helper\RealtimeManager $realtimeManager
    ) {
        $this->realtimeManager = $realtimeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var CreditmemoInterface $creditmemo */
        $creditmemo = $observer->getData('creditmemo');
        if ($creditmemo->getOrderId()) {
            $this->realtimeManager->trigger(RealtimeManager::ORDER_ENTITY, $creditmemo->getOrderId(), RealtimeManager::TYPE_CHANGE_UPDATE);
        }

    }
}