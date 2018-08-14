<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/5/17
 * Time: 2:22 PM
 */

namespace SM\Performance\Observer\BatchData;


use Magento\Framework\Event\Observer;
use SM\Performance\Helper\RealtimeManager;

/**
 * Class RegisterBatch
 *
 * @package SM\Performance\Observer\BatchData
 */
class RegisterBatch implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \SM\Performance\Helper\RealtimeManager
     */
    private $realtimeManager;

    public function __construct(\SM\Performance\Helper\RealtimeManager $realtimeManager) {
        $this->realtimeManager = $realtimeManager;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var \SM\XRetail\Controller\V1\Xretail $apiController */
        $apiController = $observer->getData('apiController');
        $path          = $apiController->getPath();

        if (in_array($path, ['take-payment', 'retail-setting'])) {
            RealtimeManager::$CAN_SEND_REAL_TIME = false;

            return;
        }

        $this->realtimeManager->useBatchData();
    }
}