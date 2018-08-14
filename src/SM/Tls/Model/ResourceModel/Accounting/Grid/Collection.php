<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 11/21/17
 * Time: 11:43
 */

namespace SM\Tls\Model\ResourceModel\Accounting\Grid;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult {

    protected $_map
        = [
            'fields' => [
                'outlet_id'  => 'sales_order.outlet_id',
                'created_at' => 'main_table.created_at']];

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable = 'sales_order_item',
        $resourceModel = 'Magento\Sales\Model\ResourceModel\Order\Item'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel, null, null);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect() {
        parent::_initSelect();
        $this->getSelect()
             ->columns(
                 [
                     'real_amount' => new \Zend_Db_Expr("main_table.row_invoiced - main_table.amount_refunded")
                 ]
             )
             ->where('main_table.row_invoiced - main_table.amount_refunded > 0')
             ->joinInner(
                 ['sales_order' => $this->getTable('sales_order')],
                 'main_table.order_id = sales_order.entity_id AND sales_order.retail_id IS NOT NULL',
                 [
                     'increment_id',
                     'outlet_id',
                     'retail_id',
                     //'reference_number',
                     'store_currency_code',
                     'user_id'
                 ]
             )->joinLeft(
                [
                    'sm_retail_transaction' => new \Zend_Db_Expr(
                        "(SELECT order_id,GROUP_CONCAT(DISTINCT payment_title SEPARATOR ', ') AS `payment_title`
                    FROM {$this->getTable('sm_retail_transaction')} as `sm_retail_transaction`
                    GROUP BY sm_retail_transaction.order_id)"
                    )],
                'main_table.order_id = sm_retail_transaction.order_id',
                [
                    'payment_title'
                ]
            );
    }
}
