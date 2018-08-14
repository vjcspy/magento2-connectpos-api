<?php
namespace SM\Shift\Model\ResourceModel;
class RetailTransaction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sm_retail_transaction','id');
    }
}
