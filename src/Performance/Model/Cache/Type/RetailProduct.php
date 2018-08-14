<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 07/03/2017
 * Time: 11:25
 */

namespace SM\Performance\Model\Cache\Type;


class RetailProduct extends \Magento\Framework\Cache\Frontend\Decorator\TagScope {

    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'retail_product';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    const CACHE_TAG = 'RETAIL_PRODUCT';

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     * @param \Magento\Framework\Event\ManagerInterface      $eventManager
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $mode
     * @param array  $tags
     *
     * @return bool
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = []) {
        $this->eventManager->dispatch('adminhtml_cache_refresh_retail_product');

        return parent::clean($mode, $tags);
    }

    static function getCacheKey($id, $store, $warehouseId=null) {
        return $id . "|" . $store . "|" . $warehouseId;
    }
}
