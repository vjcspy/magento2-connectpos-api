<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 5/16/17
 * Time: 10:39 AM
 */

namespace SM\Performance\Model\ResourceModel;


abstract class AbstractProductCache extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    /**
     * Force Primary Key not auto increment
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;
}