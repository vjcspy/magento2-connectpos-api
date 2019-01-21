<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 7/29/17
 * Time: 12:17 PM
 */

namespace SM\Performance\Model\ResourceModel\IzProduct;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected function _construct() {
        $this->_init('SM\Performance\Model\IzProduct', 'SM\Performance\Model\ResourceModel\IzProduct');
    }
}