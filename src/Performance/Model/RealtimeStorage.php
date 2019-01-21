<?php
namespace SM\Performance\Model;
class RealtimeStorage extends \Magento\Framework\Model\AbstractModel
{
    const CACHE_TAG = 'sm_realtime_storage';

    protected function _construct()
    {
        $this->_init('SM\Performance\Model\ResourceModel\RealtimeStorage');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
