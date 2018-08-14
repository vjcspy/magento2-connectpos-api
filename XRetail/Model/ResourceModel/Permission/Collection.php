<?php
namespace SM\XRetail\Model\ResourceModel\Permission;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SM\XRetail\Model\Permission','SM\XRetail\Model\ResourceModel\Permission');
    }
}
