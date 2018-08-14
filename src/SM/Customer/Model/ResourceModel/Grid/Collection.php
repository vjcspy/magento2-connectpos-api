<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 9/29/17
 * Time: 3:00 PM
 */

namespace SM\Customer\Model\ResourceModel\Grid;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_init('\Magento\Customer\Model\Customer', $resourceModel = '\Magento\Customer\Model\ResourceModel\Customer');
        $this->setMainTable(true);
        $this->setMainTable($this->_resource->getTable('customer_grid_flat'));
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            null,
            null
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}