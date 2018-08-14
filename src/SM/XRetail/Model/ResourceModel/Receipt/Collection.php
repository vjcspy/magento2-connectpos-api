<?php
namespace SM\XRetail\Model\ResourceModel\Receipt;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SM\XRetail\Model\Receipt','SM\XRetail\Model\ResourceModel\Receipt');
    }
}
