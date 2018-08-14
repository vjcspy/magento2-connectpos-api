<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 7/29/17
 * Time: 12:19 PM
 */

namespace SM\Performance\Model;


class IzProduct extends \SM\Performance\Model\AbstractProductCache {

    const CACHE_TAG = 'iz_product';

    protected function _construct() {
        $this->_init('SM\Performance\Model\ResourceModel\IzProduct');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}