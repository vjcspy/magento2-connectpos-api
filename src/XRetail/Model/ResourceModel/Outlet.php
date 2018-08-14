<?php
namespace SM\XRetail\Model\ResourceModel;
class Outlet extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sm_xretail_outlet','id');
    }
}
