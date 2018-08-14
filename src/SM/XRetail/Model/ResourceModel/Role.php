<?php
namespace SM\XRetail\Model\ResourceModel;
class Role extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sm_role','id');
    }
}
