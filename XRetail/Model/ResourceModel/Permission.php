<?php
namespace SM\XRetail\Model\ResourceModel;
class Permission extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sm_permission','id');
    }
}
