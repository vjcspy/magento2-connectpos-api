<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 08/03/2017
 * Time: 11:19
 */

namespace SM\Performance\Observer;


use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\StoreManagerInterface;
use SM\Performance\Helper\RealtimeManager;
use SM\Performance\Model\Cache\Type\RetailProduct;

/**
 * Class ModelAfterDelete
 *
 * @package SM\Performance\Observer
 */
class ModelAfterDelete implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \SM\Performance\Helper\RealtimeManager
     */
    private $realtimeManager;
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $cache;
    /**
     * @var \SM\Performance\Observer\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \SM\Performance\Helper\CacheKeeper
     */
    private $cacheKeeper;

    /**
     * ModelAfterDelete constructor.
     *
     * @param \SM\Performance\Helper\RealtimeManager $realtimeManager
     */
    public function __construct(
        RealtimeManager $realtimeManager,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \SM\Performance\Helper\CacheKeeper $cacheKeeper
    ) {
        $this->cacheKeeper     = $cacheKeeper;
        $this->storeManager    = $storeManager;
        $this->cache           = $cache;
        $this->realtimeManager = $realtimeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $object = $observer->getData('object');

        if ($object instanceof Customer)
            $this->realtimeManager->trigger(RealtimeManager::CUSTOMER_ENTITY, $object->getId(), RealtimeManager::TYPE_CHANGE_REMOVE);

        if ($object instanceof Category)
            $this->realtimeManager->trigger(RealtimeManager::CATEGORY_ENTITY, $object->getId(), RealtimeManager::TYPE_CHANGE_REMOVE);

        if ($object instanceof \Magento\Customer\Model\Group)
            $this->realtimeManager->trigger(
                RealtimeManager::CUSTOMER_GROUP,
                $object->getData('customer_group_id'),
                RealtimeManager::TYPE_CHANGE_REMOVE);

        if ($object instanceof Product) {
            $this->cacheKeeper->deleteEntity($object->getId());
            $this->realtimeManager->trigger(RealtimeManager::PRODUCT_ENTITY, $object->getId(), RealtimeManager::TYPE_CHANGE_REMOVE);
        }
    }
}
