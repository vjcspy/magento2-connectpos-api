<?php
namespace SM\Shift\Model\ResourceModel;

class Shift extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct() {
        $this->_init('sm_shift_shift', 'id');
    }
}
