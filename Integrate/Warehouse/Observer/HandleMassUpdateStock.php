<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 3/27/18
 * Time: 10:46
 */

namespace SM\Integrate\Warehouse\Observer;


use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use SM\Performance\Helper\RealtimeManager;

class HandleMassUpdateStock implements ObserverInterface {

    /**
     * @var \SM\Performance\Helper\RealtimeManager
     */
    private $realtimeManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * ModelAfterSave constructor.
     *
     * @param \SM\Performance\Helper\RealtimeManager $realtimeManager
     */
    public function __construct(
        RealtimeManager $realtimeManager,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager    = $storeManager;
        $this->realtimeManager = $realtimeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $object = $observer->getData('object');

        if (class_exists("BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item")) {
            if ($object instanceof \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item) {
                $this->realtimeManager->trigger(
                    RealtimeManager::PRODUCT_ENTITY,
                    $object->getData('wi_product_id'),
                    RealtimeManager::TYPE_CHANGE_UPDATE);
            }
        }
    }
}
