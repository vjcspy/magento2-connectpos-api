<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 13/03/2017
 * Time: 14:39
 */

namespace SM\Performance\Observer;


use SM\Performance\Helper\RealtimeManager;

class ChangeSetting implements \Magento\Framework\Event\ObserverInterface {

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
        $this->realtimeManager->trigger(RealtimeManager::SETTING_ENTITY, "all", RealtimeManager::TYPE_CHANGE_UPDATE);
    }
}