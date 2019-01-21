<?php
namespace SM\XRetail\Model\ResourceModel\Outlet\Register;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected function _construct() {
        $this->_init('SM\XRetail\Model\Outlet\Register', 'SM\XRetail\Model\ResourceModel\Outlet\Register');
    }
}
