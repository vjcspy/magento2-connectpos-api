<?php
namespace SM\Shift\Model\ResourceModel;

class ShiftInOut extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct() {
        $this->_init('sm_shift_shiftinout', 'id');
    }
}
