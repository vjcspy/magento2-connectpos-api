<?php
namespace SM\XRetail\Model\ResourceModel\Outlet;

class Register extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct() {
        $this->_init('sm_xretail_register', 'id');
    }
}
