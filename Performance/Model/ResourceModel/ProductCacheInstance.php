<?php

namespace SM\Performance\Model\ResourceModel;

class ProductCacheInstance extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct() {
        $this->_init('sm_performance_product_cache_instance', 'id');
    }
}
