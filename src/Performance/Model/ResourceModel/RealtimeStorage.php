<?php
namespace SM\Performance\Model\ResourceModel;

class RealtimeStorage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct() {
        $this->_init('sm_realtime_storage', 'id');
    }
}
