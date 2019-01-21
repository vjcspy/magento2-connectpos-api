<?php

namespace SM\Performance\Observer;


use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use SM\Performance\Helper\RealtimeManager;

/**
 * Class ModelAfterSave
 *
 * @package SM\Performance\Observer
 */
class ModelAfterSave implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \SM\Performance\Helper\RealtimeManager
     */
    private $realtimeManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $cache;
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $context;
    /**
     * @var \SM\XRetail\Helper\Data
     */
    private $retailHepler;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $_fieldOriginalProductCheck
        = [
            "name",
            "sku",
            "image",
            "special_price",
            "price"
        ];

    static $SUPPORT_CHECK_REALTIME_API = false;

    /**
     * ModelAfterSave constructor.
     *
     * @param \SM\Performance\Helper\RealtimeManager $realtimeManager
     */
    public function __construct(
        RealtimeManager $realtimeManager,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \SM\XRetail\Helper\Data $helperData,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager   = $objectManager;
        $this->storeManager    = $storeManager;
        $this->realtimeManager = $realtimeManager;
        $this->cache           = $cache;
        $this->retailHepler    = $helperData;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $object = $observer->getData('object');

        $request = $this->objectManager->get("Magento\Framework\App\RequestInterface");

        if ($object instanceof Customer)
            $this->realtimeManager->trigger(RealtimeManager::CUSTOMER_ENTITY, $object->getId(), RealtimeManager::TYPE_CHANGE_UPDATE);

        if ($object instanceof Category) {
            $this->realtimeManager->trigger(RealtimeManager::CATEGORY_ENTITY, $object->getId(), RealtimeManager::TYPE_CHANGE_UPDATE);
        }

        // move category
        if ($observer->getData('category') instanceof Category && $observer->getData('category_id')) {
            $this->realtimeManager->trigger(RealtimeManager::CATEGORY_ENTITY, $observer->getData('category_id'), RealtimeManager::TYPE_CHANGE_UPDATE);
        }

        if ($object instanceof \Magento\Customer\Model\Group)
            $this->realtimeManager->trigger(
                RealtimeManager::CUSTOMER_GROUP,
                $object->getData('customer_group_id'),
                RealtimeManager::TYPE_CHANGE_UPDATE);

        if ($object instanceof Product) {

            if (ModelAfterSave::$SUPPORT_CHECK_REALTIME_API) {
                if ($request && strpos($request->getPathInfo(), "rest/V1/products") != false) {
                    if (!$this->isDataChange($object)) {
                        return;
                    }
                }
            }

            $ids = [];
            array_push($ids, $object->getId());
            if ($object->getTypeId() == 'configurable') {
                /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $instanceType */
                $instanceType = $object->getTypeInstance();
                $childIds     = $instanceType->getChildrenIds($object->getId());
                foreach ($childIds as $_ids) {
                    $ids = array_merge($ids, $_ids);
                }
            }
            $this->realtimeManager->trigger(RealtimeManager::PRODUCT_ENTITY, join(",", array_unique($ids)), RealtimeManager::TYPE_CHANGE_UPDATE);
        }

        if (\SM\Sales\Repositories\OrderManagement::$SAVE_ORDER === true && $object instanceof \Magento\Quote\Model\Quote\Item) {
            $this->realtimeManager->trigger(
                RealtimeManager::PRODUCT_ENTITY,
                $object->getProduct()->getId(),
                RealtimeManager::TYPE_CHANGE_UPDATE);
        }

        if ($object instanceof \Magento\CatalogInventory\Model\Stock\Item) {
            if ($request && strpos($request->getPathInfo(), "rest/V1/products") != false) {
                //$this->retailHepler->addLog(
                //    "updated_via_api: " . $object->getId() . " - " . date("Y-m-d h:i:sa"),
                //    \Zend\Log\Logger::INFO,
                //    "api_update_stock.log");

                return;
            }
            $this->realtimeManager->trigger(
                RealtimeManager::PRODUCT_ENTITY,
                $object->getData('product_id'),
                RealtimeManager::TYPE_CHANGE_UPDATE);
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    protected function isDataChange(Product $product) {
        foreach ($this->_fieldOriginalProductCheck as $field) {
            if ($product->getOrigData($field) != $product->getData($field)) {
                return true;
            }
        }

        return false;
    }
}
