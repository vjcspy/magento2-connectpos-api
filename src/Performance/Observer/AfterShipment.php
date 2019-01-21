<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 14/03/2017
 * Time: 18:08
 */

namespace SM\Performance\Observer;


use SM\Performance\Helper\RealtimeManager;

class AfterShipment implements \Magento\Framework\Event\ObserverInterface {

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

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();

        $this->realtimeManager->trigger(RealtimeManager::ORDER_ENTITY, $order->getId(), RealtimeManager::TYPE_CHANGE_NEW);
    }
}