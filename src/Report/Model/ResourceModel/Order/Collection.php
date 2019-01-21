<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/20/17
 * Time: 5:02 PM
 */

namespace SM\Report\Model\ResourceModel\Order;

use SM\Report\Helper\Data;
use SM\Shift\Model\RetailTransactionFactory;

class Collection extends \Magento\Reports\Model\ResourceModel\Order\Collection {


    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $orderItemCollectionFactory;

    /**
     * @var \SM\Shift\Model\ResourceModel\RetailTransaction\CollectionFactory
     */
    protected $retailTransactionCollectionFactory;

    /**
     * @var \SM\Report\Helper\Data
     */
    protected $reportHelper;

    private $_discountAmountExpression;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    protected $_grandTotal;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory                  $entityFactory
     * @param \Psr\Log\LoggerInterface                                          $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface      $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                         $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\DB\Helper                                      $coreResourceHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface                $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface                        $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface              $localeDate
     * @param \Magento\Sales\Model\Order\Config                                 $orderConfig
     * @param \Magento\Sales\Model\ResourceModel\Report\OrderFactory            $reportOrderFactory
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null               $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null         $resource
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory   $collectionFactory
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $collectionFactory,
        \SM\Shift\Model\ResourceModel\RetailTransaction\CollectionFactory $retailTransactionCollectionFactory,
        \SM\Report\Helper\Data $reportHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\ResourceModel\Report\OrderFactory $reportOrderFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $coreResourceHelper,
            $scopeConfig,
            $storeManager,
            $localeDate,
            $orderConfig,
            $reportOrderFactory,
            $connection,
            $resource);
        $this->orderItemCollectionFactory         = $collectionFactory;
        $this->reportHelper                       = $reportHelper;
        $this->retailTransactionCollectionFactory = $retailTransactionCollectionFactory;
        $this->_eavAttribute                      = $eavAttribute;
    }

    /**
     * @param string $range
     * @param mixed  $customStart
     * @param mixed  $customEnd
     * @param int    $isFilter
     *
     * @return $this
     */
    public function prepareSummary($range, $customStart, $customEnd, $isFilter = 0) {
        $this->_prepareSummaryReport($range, $customStart, $customEnd, $isFilter);

        return $this;
    }

    /**
     * Get range expression
     *
     * @param string $range
     * @return \Zend_Db_Expr
     */
    protected function _getRangeExpression($range)
    {
        switch ($range) {
            case '24h':
                $expression = $this->getConnection()->getConcatSql(
                    [
                        $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d %H:'),
                        $this->getConnection()->quote('00'),
                    ]
                );
                break;
            case '7d':
            case '1m':
            case '6w':
                $expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d');
                break;
            case '6m':
            case '1y':
            case '2y':
            case 'custom':
            default:
                $expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m');
                break;
        }

        return $expression;
    }

    /**
     * @param $range
     * @param $customStart
     * @param $customEnd
     * @param $isFilter
     *
     * @return $this
     */
    protected function _prepareSummaryReport($range, $customStart, $customEnd, $isFilter) {
        $this->setMainTable('sales_order');
        $connection = $this->getConnection();

        /**
         * Reset all columns, because result will group only by 'created_at' field
         */
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);

        $salesAmountExpression = $this->_getSalesAmountExpression();
        if ($isFilter == 0) {
            $this->getSelect()->columns(
                [
                    'revenue' => new \Zend_Db_Expr(
                        sprintf(
                            'SUM((%s) * %s)',
                            $salesAmountExpression,
                            $connection->getIfNullSql('main_table.base_to_global_rate', 0)
                        )
                    ),
                ]
            );
        }
        else {
            $this->getSelect()->columns(['revenue' => new \Zend_Db_Expr(sprintf('SUM(%s)', $salesAmountExpression))]);
        }

        $dateRange = $this->getDateRange($range, $customStart, $customEnd);

        $tzRangeOffsetExpression = $this->_getTZRangeOffsetExpression(
            $range,
            'created_at',
            $dateRange['from'],
            $dateRange['to']
        );

        $discountAmountExpression = $this->_getDiscountAmountExpression();
        $grandTotal = $this->_getGrandTotal();
        $discountPerItemExpression =  new \Magento\Framework\DataObject(
            [
                'expression' => '-(%s)',
                'arguments'  => [
                    $connection->getIfNullSql('main_table.base_discount_per_item', 0),
                ],
            ]
        );
        $discountPerItem =  vsprintf(
            $discountPerItemExpression->getExpression(),
            $discountPerItemExpression->getArguments()
        );
        $discountRefundedExpression =  new \Magento\Framework\DataObject(
            [
                'expression' => '-(%s)',
                'arguments'  => [
                    $connection->getIfNullSql('main_table.base_discount_refunded', 0),
                ],
            ]
        );
        $discountRefunded =  vsprintf(
            $discountRefundedExpression->getExpression(),
            $discountRefundedExpression->getArguments()
        );
        $this->getSelect()
             ->columns(
                 [
                     'quantity'          => 'COUNT(main_table.entity_id)',
                     'range'             => $tzRangeOffsetExpression,
                     'list_customer'     => 'GROUP_CONCAT( DISTINCT main_table.customer_id)',
                     'customer_count'    => 'COUNT(DISTINCT main_table.customer_id)',
                     'grand_total'            => new \Zend_Db_Expr(
                         sprintf(
                             $connection->getIfNullSql('%s - %s', 0),
                             $connection->getIfNullSql('SUM(main_table.base_total_invoiced)', 0),
                             $connection->getIfNullSql('SUM(main_table.base_total_refunded)', 0)
                         )
                     ),
                     'discount' => new \Zend_Db_Expr(
                         sprintf(
                             'SUM(%s - %s)',
                             $discountAmountExpression,
                             $discountRefunded)),
                     'subtotal_incl_tax' => new \Zend_Db_Expr($connection->getIfNullSql('main_table.base_subtotal_incl_tax', 0)),
                     'discount_percent'  => new \Zend_Db_Expr(
                         vsprintf(
                             'SUM(%s - %s)/SUM(%s + %s -%s)',
                             [
                                 $discountAmountExpression,
                                 $discountRefunded,
                                 $grandTotal,
                                 $discountAmountExpression,
                                 $discountRefunded])
                             //[$discountAmountExpression, $discountPerItem, $connection->getIfNullSql('main_table.base_subtotal_incl_tax', 0.01)])
                     ),
                     'average_sales'     => new \Zend_Db_Expr(sprintf('SUM(%s)/COUNT(main_table.entity_id)', $salesAmountExpression)),
                     'store_id'          => 'main_table.store_id',
                     'outlet_id'         => 'main_table.outlet_id'
                 ]
             )->order(
                'range',
                \Magento\Framework\DB\Select::SQL_ASC
            )->group(
                $tzRangeOffsetExpression
            );
        //$this->getSelect()->where(
        //    'main_table.state NOT IN (?)',
        //    [\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, \Magento\Sales\Model\Order::STATE_NEW]
        //);
        $this->addFieldToFilter('retail_status', [['nin' => [11, 12, 13]], ['null' => true]]);
        $this->addFieldToFilter('base_total_invoiced', ['neq' => 'NULL']);
        $this->addFieldToFilter('created_at', $dateRange);
        return $this;
    }

    /**
     * Get sales amount expression
     *
     * @return string
     */
    protected function _getGrandTotal()
    {
        if (null === $this->_grandTotal) {
            $connection = $this->getConnection();
            $expressionTransferObject = new \Magento\Framework\DataObject(
                [
                    'expression' => '%s - %s',
                    'arguments' => [
                        $connection->getIfNullSql('main_table.base_total_invoiced', 0),
                        $connection->getIfNullSql('main_table.base_total_refunded', 0),
                    ],
                ]
            );

            $this->_eventManager->dispatch(
                'sales_prepare_amount_expression',
                ['collection' => $this, 'expression_object' => $expressionTransferObject]
            );
            $this->_grandTotal = vsprintf(
                $expressionTransferObject->getExpression(),
                $expressionTransferObject->getArguments()
            );
        }

        return $this->_grandTotal;
    }

    /**
     * @return mixed
     */
    protected function _getDiscountAmountExpression() {
        if (null === $this->_discountAmountExpression) {
            $connection               = $this->getConnection();
            $expressionTransferObject = new \Magento\Framework\DataObject(
                [
                    'expression' => '-(%s)',
                    'arguments'  => [
                        $connection->getIfNullSql('main_table.base_discount_invoiced', 0),
                    ],
                ]
            );

            $this->_eventManager->dispatch(
                'sales_prepare_discount_amount_expression',
                ['collection' => $this, 'expression_object' => $expressionTransferObject]
            );
            $this->_discountAmountExpression = vsprintf(
                $expressionTransferObject->getExpression(),
                $expressionTransferObject->getArguments()
            );
        }

        return $this->_discountAmountExpression;
    }

    /**
     * Calculate From and To dates (or times) by given period
     *
     * @param string $range
     * @param string $customStart
     * @param string $customEnd
     * @param bool   $returnObjects
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false) {
        $array_date_start = explode('/', $customStart);
        $array_date_end   = explode('/', $customEnd);


        $date_start_GMT = $this->_localeDate->date($array_date_start[0], null, false);
        $date_end_GMT   = $this->_localeDate->date($array_date_end[0], null, false);

        $dateEnd   = new \DateTime($array_date_end[1]);
        $dateStart = new \DateTime($array_date_start[1]);


        // go to the end of a day
        $dateEnd->setTime(23, 59, 59);

        $dateStart->setTime(0, 0, 0);

        switch ($range) {
            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->modify('-6 days');

                $date_start_GMT->modify('-6 days');
                break;
            case '6w':
                for ($i = 1; $i <= 5; $i++) {
                    $dateStart->modify('-7 days');
                    $date_start_GMT->modify('-7 days');
                }
                break;
            case '6m':
                for ($i = 1; $i <= 5; $i++) {
                    $dateStart->modify('-1 month');
                    $date_start_GMT->modify('-1 month');
                }
                break;
            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd   = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(
                    ',',
                    $this->_scopeConfig->getValue(
                        'reports/dashboard/ytd_start',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                );
                $startMonth    = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay      = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setDate($dateStart->format('Y'), $startMonth, $startDay);
                if ($range == '2y') {
                    $dateStart->modify('-1 year');
                }
                break;
        }
        if ($returnObjects) {
            return [$dateStart, $dateEnd];
        }
        else {
            return ['from' => $date_start_GMT, 'to' => $date_end_GMT, 'datetime' => true];
        }
    }

    /**
     * @param $data
     * @param $dateRanger
     *
     * @return $this
     */
    public function getSalesReportFromOrderCollection(
        $data,
        $dateStart,
        $dateEnd,
        $isSelectMagentoStatus = false,
        $if_filter_total_value = false,
        $itemDetail = null,
        $extra_info = null
    ) {
        // transaction count : là đếm số lần order thực hiện 1 payment ( vd :
        $typeReport = $data['type'];
        $dataFilter = $data['filter'];


        $this->setMainTable('sales_order');
        $connection = $this->getResource()->getConnection();
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);

        if ($itemDetail != null && $typeReport == 'outlet' || $extra_info != null) {
        }
        else {
            $this->joinOrderItemTable();
        }

        //$this->joinTransactionCountTable();
        // add filter date time
        $this->reportHelper->addDateRangerFilter($this, $dateStart, $dateEnd);


        // in case select detail magento status
        if ($typeReport == 'order_status' && $isSelectMagentoStatus && $data['item_filter'] == "magento_status") {
            $this->addFieldToFilter('retail_status', ['null' => true]);
        }
        else {
            // khong lay order nhung order la PARTIALLY payment
            $this->addFieldToFilter('retail_status', [['nin' => [11, 12, 13]], ['null' => true]]);
            $this->addFieldToFilter('base_total_invoiced', ['neq' => 'NULL']);
        }
        //$this->addFieldToFilter('retail_status', [['nin' => [1, 2, 3, 4, 5, 6]]]);
        $globalTz = $this->reportHelper->getTimezone(true);
        switch ($typeReport) {
            case "sales_summary":
                $this->addAttributeToSelect('entity_id');
                break;
            case "user":
                // cai nay chi lay user_id .Khi dua data len client se get user name sau
                $this->addAttributeToSelect('user_id');
                //$this->getSelect()->columns(['retail_user' => "GROUP_CONCAT(main_table.retail_user)"]);
                $this->getSelect()->group('user_id');
                break;
            case 'category':
                $this->getSelect()->joinLeft(
                    ['sales_order_item' => $this->getTable('sales_order_item')],
                    'sales_order_item.order_id = main_table.entity_id');
                $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
                $this->addFieldToFilter('sales_order_item.product_type', ['simple', 'virtual', 'configurable', 'bundle']);
                $this->addFieldToFilter('sales_order_item.parent_item_id', ['null' => true]);
                $this->getSelect()->joinLeft(
                    ['category_product' => $this->getTable('catalog_category_product')],
                    'category_product.product_id = sales_order_item.product_id',
                    ['category_id' => 'category_product.category_id']);
                $category_att_id = $this->_eavAttribute->getIdByCode('catalog_category', 'name');
                $this->getSelect()->joinLeft(
                    ['category_varchar' => $this->getTable('catalog_category_entity_varchar')],
                    'category_product.category_id = category_varchar.row_id AND category_varchar.store_id = 0  AND category_varchar.attribute_id ='
                    . $category_att_id,
                    ['category_name' => 'category_varchar.value']);
                if ($extra_info == 'region') {
                    $this->getSelect()->joinLeft(
                        ['sm_region_outlet' => $this->getTable('sm_region_outlet')],
                        '`sm_region_outlet`.outlet_id=main_table.outlet_id',
                        ['region_id']);
                    $this->getSelect()->joinLeft(
                        ['sm_region' => $this->getTable('sm_region')],
                        '`sm_region`.id=`sm_region_outlet`.region_id',
                        ['region_name' => 'sm_region.region_name']);
                }
                else if ($extra_info == 'outlet') {
                    $this->getSelect()->joinLeft(
                        ['sm_outlet' => $this->getTable('sm_xretail_outlet')],
                        'sm_outlet.id = outlet_id',
                        ['name' => 'sm_outlet.name']);
                }
                if ($itemDetail == "N/A") {
                    $this->addFieldToFilter('category_id', ['null' => true]);
                }
                else {
                    $this->addFieldToFilter('category_id', $itemDetail);
                }
                if ($extra_info == 'region') {
                    $this->getSelect()->group('region_id');
                }
                else if ($extra_info == 'outlet') {
                    $this->addAttributeToSelect('outlet_id');
                    $this->getSelect()->group('outlet_id');
                }
                break;
            case "outlet":
                if ($itemDetail == null) {
                    $this->getSelect()->joinLeft(
                        ['sm_outlet' => $this->getTable('sm_xretail_outlet')],
                        'sm_outlet.id = outlet_id',
                        ['name' => 'sm_outlet.name']);
                    $this->addAttributeToSelect('outlet_id');
                    $this->getSelect()->group('outlet_id');
                }
                else {
                    $this->getSelect()->joinLeft(
                        ['sales_order_item' => $this->getTable('sales_order_item')],
                        'sales_order_item.order_id = main_table.entity_id');
                    $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
                    $this->addFieldToFilter('sales_order_item.product_type', ['simple', 'virtual', 'configurable', 'bundle']);
                    $this->addFieldToFilter('sales_order_item.parent_item_id', ['null' => true]);
                    $this->getSelect()->joinLeft(
                        ['category_product' => $this->getTable('catalog_category_product')],
                        'category_product.product_id = sales_order_item.product_id',
                        ['category_id' => 'category_product.category_id']);
                    $category_att_id = $this->_eavAttribute->getIdByCode('catalog_category', 'name');
                    $this->getSelect()->joinLeft(
                        ['category_varchar' => $this->getTable('catalog_category_entity_varchar')],
                        'category_product.category_id = category_varchar.row_id AND category_varchar.store_id = 0  AND category_varchar.attribute_id ='
                        . $category_att_id,
                        ['category_name' => 'category_varchar.value']);
                    $this->getSelect()->joinLeft(
                        ['sm_outlet' => $this->getTable('sm_xretail_outlet')],
                        'sm_outlet.id = outlet_id',
                        ['name' => 'sm_outlet.name']);
                    if ($itemDetail == "N/A") {
                        $this->addFieldToFilter('outlet_id', ['null' => true]);
                    }
                    else {
                        $this->addFieldToFilter('outlet_id', $itemDetail);
                    }
                    $this->getSelect()->group('category_id');
                }
                break;
            case "region":
                if ($itemDetail == null) {
                    $this->getSelect()->joinLeft(
                        ['sm_region_outlet' => $this->getTable('sm_region_outlet')],
                        '`sm_region_outlet`.outlet_id=main_table.outlet_id',
                        ['region_id']);
                    $this->getSelect()->joinLeft(
                        ['sm_region' => $this->getTable('sm_region')],
                        '`sm_region`.id=`sm_region_outlet`.region_id',
                        ['region_name' => 'sm_region.region_name']);
                    $this->getSelect()->group('region_id');
                }
                else {
                    $this->getSelect()->joinLeft(
                        ['sm_region_outlet' => $this->getTable('sm_region_outlet')],
                        '`sm_region_outlet`.outlet_id=main_table.outlet_id',
                        ['region_id']);
                    $this->getSelect()->joinLeft(
                        ['sm_region' => $this->getTable('sm_region')],
                        '`sm_region`.id=`sm_region_outlet`.region_id',
                        ['region_name' => 'sm_region.region_name']);
                    if ($itemDetail == "N/A") {
                        $this->addFieldToFilter('region_id', ['null' => true]);
                    }
                    else {
                        $this->addFieldToFilter('region_id', $itemDetail);
                    }
                    $this->addAttributeToSelect('customer_id');
                    $this->getSelect()->group('customer_id');
                }
                break;
            case "reference_number":
                $this->getSelect()->joinLeft(
                    ['sm_outlet' => $this->getTable('sm_xretail_outlet')],
                    'sm_outlet.id = outlet_id',
                    ['name' => 'sm_outlet.name']);
                $this->addAttributeToSelect('reference_number');
                $this->getSelect()->group('reference_number');
                break;
            case "register":
                $this->getSelect()->joinLeft(
                    ['sregister' => $this->getTable('sm_xretail_register')],
                    'sregister.id = register_id',
                    ['name' => 'sregister.name']);
                $this->addAttributeToSelect('register_id');
                $this->getSelect()->group('register_id');
                break;
            case "customer" :
                $this->getSelect()->joinLeft(
                    ['cgroup' => $this->getTable('customer_group')],
                    'cgroup.customer_group_id = main_table.customer_group_id',
                    ['customer_group_code' => 'cgroup.customer_group_code']
                );
                $customer_telephone_att_id = $this->_eavAttribute->getIdByCode('customer', 'retail_telephone');
                if (!!$customer_telephone_att_id) {
                    $this->getSelect()->joinLeft(
                        ['cusvarchar' => $this->getTable('customer_entity_varchar')],
                        'cusvarchar.entity_id = main_table.customer_id AND `cusvarchar`.`attribute_id` = ' . $customer_telephone_att_id,
                        ['customer_telephone' => 'cusvarchar.value']
                    );
                }
                $this->addAttributeToSelect('customer_email');
                $this->addAttributeToSelect('customer_id');
                $this->getSelect()->group('customer_id');
                break;
            case "customer_group" :
                $this->getSelect()->joinLeft(
                    ['cgroup' => $this->getTable('customer_group')],
                    'cgroup.customer_group_id = main_table.customer_group_id',
                    ['customer_group_code' => 'cgroup.customer_group_code']);
                $this->addAttributeToSelect('customer_group_id');
                $this->getSelect()->group('customer_group_code');
                break;
            case "order_status" :
                if ($isSelectMagentoStatus) {
                    $this->addAttributeToSelect('status');
                    $this->getSelect()->group('status');
                }
                else {
                    $this->addAttributeToSelect('retail_status');
                    $this->getSelect()->group('retail_status');
                }
                break;
            case "payment_method" :
                $this->getSelect()->joinLeft(
                    ['spayment' => $this->getTable('sales_order_payment')],
                    'spayment.parent_id =  main_table.entity_id',
                    [
                        'payment_method' => 'spayment.method',
                        'payment_data'   => 'spayment.additional_information']);
                //$this->addAttributeToSelect('payment_method');
                $this->getSelect()->group('payment_method');
                break;
            case "shipping_method" :
                $this->addAttributeToSelect('shipping_method');
                $this->addAttributeToSelect('shipping_description');
                $this->getSelect()->group('shipping_method');
                break;
            case "currency":
                $this->addAttributeToSelect('order_currency_code');
                $this->getSelect()->group('order_currency_code');
                break;
            case "magento_storeview":
                $this->addAttributeToSelect('store_id');
                $this->getSelect()->joinLeft(
                    ['store_int' => $this->getTable('store')],
                    '`store_int`.store_id=main_table.store_id',
                    ['store_name' => 'store_int.name']);
                $this->getSelect()->group('store_id');
                break;
            case "magento_website":
                $this->getSelect()->joinLeft(
                    ['website_int' => $this->getTable('store')],
                    '`website_int`.store_id=main_table.store_id',
                    ['website_id']);
                $this->getSelect()->joinLeft(
                    ['website_name' => $this->getTable('store_website')],
                    '`website_name`.website_id=`website_int`.website_id',
                    ['website_name' => 'website_name.name']);
                $this->getSelect()->group('website_id');
                break;
            case "day_of_week" :
                $this->getSelect()->columns(['day_of_week' => "DAYOFWEEK(CONVERT_TZ(main_table.created_at, '+00:00', '{$globalTz}'))"]);
                $this->getSelect()->group('day_of_week');
                break;
            case "hour" :
                $this->getSelect()->columns(['hour' => "HOUR(CONVERT_TZ(main_table.created_at, '+00:00', '{$globalTz}'))"]);
                $this->getSelect()->group('hour');
                break;
        }
        if ($itemDetail != null && $typeReport == 'outlet' || $extra_info != null) {
            $this->addDataToSelectItem();
        }
        else {
            $this->addDataToSelect();
        }
        $this->reportHelper->filterByColumn($this, $dataFilter, $if_filter_total_value);

        return $this;
    }

    private function addDataToSelect() {
        $connection = $this->getResource()->getConnection();
        $select     = $this->getSelect();
        $select->columns(
            [
                'total_shipping_amount'  => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('%s - %s', 0),
                        $connection->getIfNullSql('SUM(main_table.base_shipping_amount)', 0),
                        $connection->getIfNullSql('SUM(main_table.base_shipping_tax_amount)', 0)
                    )
                ),
                'base_total_invoiced'    => $connection->getIfNullSql('SUM(main_table.base_total_invoiced)', '0'),
                'revenue'                => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('%s - %s - %s - (%s - %s - %s)', 0),
                        $connection->getIfNullSql('SUM(main_table.base_total_invoiced)', '0'),
                        $connection->getIfNullSql('SUM(main_table.base_tax_amount)', '0'),
                        $connection->getIfNullSql('SUM(main_table.base_shipping_invoiced)', '0'),
                        $connection->getIfNullSql('SUM(main_table.base_total_refunded)', '0'),
                        $connection->getIfNullSql('SUM(main_table.base_tax_refunded)', '0'),
                        $connection->getIfNullSql('SUM(main_table.base_shipping_refunded)', '0')
                    )
                ),
                'total_for_discount_percent'                => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('%s - %s', 0),
                        $connection->getIfNullSql('SUM(main_table.base_total_invoiced)', 0),
                        $connection->getIfNullSql('SUM(main_table.base_total_refunded)', 0)
                    )
                ),
                'grand_total'            => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('%s - %s', 0),
                        $connection->getIfNullSql('SUM(main_table.base_total_invoiced)', 0),
                        $connection->getIfNullSql('SUM(main_table.base_total_refunded)', 0)
                    )
                ),
                'base_row_total_product' => $connection->getIfNullSql('SUM(main_table.base_total_invoiced)', '0'),
                //'total_tax'              => $connection->getIfNullSql('SUM(main_table.base_tax_amount)', '0'),
                'total_tax'             => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('SUM(%s - %s)', 0),
                        $connection->getIfNullSql('main_table.base_tax_amount', '0'),
                        $connection->getIfNullSql('main_table.base_tax_refunded', '0')
                    )
                ),
                'total_cart_size'        => $connection->getIfNullSql('SUM(main_table.total_item_count)', '0'),
                'cart_size'              => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('%s / %s', 0),
                        $connection->getIfNullSql('SUM(main_table.total_qty_ordered)', '0'),
                        'COUNT(main_table.entity_id)'
                    )
                ),
                'customer_count'         => $connection->getIfNullSql('COUNT(distinct main_table.customer_id)', '0'),
                'discount_amount'        => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('%s - %s', 0),
                        $connection->getIfNullSql('SUM(main_table.base_discount_invoiced)', '0'),
                        $connection->getIfNullSql('SUM(main_table.base_discount_refunded)','0')
                    )
                ),
                'first_sale'             => "MIN(main_table.created_at)",
                'item_sold'              => $connection->getIfNullSql('SUM(main_table.total_qty_ordered-items_order.refunded_items_count)', '0'),
                'last_sale'              => "MAX(main_table.created_at)",
                'order_count'            => 'COUNT(main_table.entity_id)',
                'shipping_amount'        => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('SUM(%s - %s)', 0),
                        $connection->getIfNullSql('main_table.base_shipping_invoiced', '0'),
                        $connection->getIfNullSql('main_table.base_shipping_refunded', '0')
                    )),
                'shipping_tax'           => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('SUM(%s - %s)', 0),
                        $connection->getIfNullSql('main_table.base_shipping_tax_amount', '0'),
                        $connection->getIfNullSql('main_table.base_shipping_tax_refunded', '0')
                    )),
                'shipping_tax_refunded'  => $connection->getIfNullSql('SUM(main_table.base_shipping_tax_refunded)', '0'),
                'subtotal_refunded'      => $connection->getIfNullSql('SUM(main_table.base_subtotal_refunded)', '0'),
                'total_refunded'         => $connection->getIfNullSql('SUM(main_table.base_total_refunded)', '0')
            ]);

        //$select->columns(
        //    [
        //        'gross_profit' => new \Zend_Db_Expr(
        //            sprintf(
        //                '%s - %s',
        //                $connection->getIfNullSql('revenue', '0'),
        //                $connection->getIfNullSql('total_cost', '0')
        //            )
        //        )]);
        //$this->setOrder('grand_total', 'ASC');
        //$this->setOrder('grand_total', 'DESC');
        return $this;
    }

    private function addDataToSelectItem() {
        $connection = $this->getResource()->getConnection();
        $select     = $this->getSelect();
        $select->columns(
            [
                'revenue'                => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('SUM(%s - %s -(%s - %s))', 0),
                        $connection->getIfNullSql('sales_order_item.base_row_total', '0'),
                        $connection->getIfNullSql('sales_order_item.base_discount_invoiced', '0'),
                        $connection->getIfNullSql('sales_order_item.base_amount_refunded', '0'),
                        $connection->getIfNullSql('sales_order_item.base_discount_refunded', '0')
                    )
                ),
                'base_row_total_product' => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('SUM(%s + %s- %s)', 0),
                        $connection->getIfNullSql('sales_order_item.base_row_total', '0'),
                        $connection->getIfNullSql('sales_order_item.base_tax_amount', '0'),
                        $connection->getIfNullSql('sales_order_item.base_discount_invoiced', '0')
                    )
                ),
                'grand_total'            => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('SUM(%s + %s- %s - (%s + %s - %s))', 0),
                        $connection->getIfNullSql('sales_order_item.base_row_total', '0'),
                        $connection->getIfNullSql('sales_order_item.base_tax_amount', '0'),
                        $connection->getIfNullSql('sales_order_item.base_discount_invoiced', '0'),
                        $connection->getIfNullSql('sales_order_item.base_amount_refunded', 0),
                        $connection->getIfNullSql('sales_order_item.base_tax_refunded', 0),
                        $connection->getIfNullSql('sales_order_item.base_discount_refunded', '0')
                    )
                ),
                //'total_tax'              => $connection->getIfNullSql('SUM(sales_order_item.base_tax_amount)', '0'),
                'total_tax'             => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('SUM(%s - %s)', 0),
                        $connection->getIfNullSql('sales_order_item.base_tax_amount', '0'),
                        $connection->getIfNullSql('sales_order_item.base_tax_refunded', '0')
                    )
                ),
                'total_cost'             => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('SUM(%s * %s)', 0),
                        $connection->getIfNullSql('sales_order_item.qty_ordered', '0'),
                        $connection->getIfNullSql('sales_order_item.base_cost', '0')
                    )
                ),
                //'total_cost' => $connection->getIfNullSql('SUM(main_table.qty_ordered) * SUM(main_table.base_cost)',0),
                'total_cart_size'        => $connection->getIfNullSql('SUM(sales_order_item.qty_ordered)', '0'),
                'cart_size'              => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('%s / %s', 0),
                        $connection->getIfNullSql('SUM(sales_order_item.qty_ordered)', '0'),
                        'COUNT(distinct sales_order_item.order_id)'
                    )
                ),
                'customer_count'         => $connection->getIfNullSql('COUNT(distinct main_table.customer_id)', '0'),
                //'discount_amount'        => $connection->getIfNullSql('SUM(-sales_order_item.base_discount_amount)', '0'),
                'discount_amount'             => new \Zend_Db_Expr(
                    sprintf(
                        $connection->getIfNullSql('SUM(%s - %s)', 0),
                        $connection->getIfNullSql('-sales_order_item.base_discount_amount', '0'),
                        $connection->getIfNullSql('-sales_order_item.base_discount_refunded', '0')
                    )
                ),
                'first_sale'             => "MIN(sales_order_item.created_at)",
                'item_sold'              => $connection->getIfNullSql('SUM(sales_order_item.qty_ordered-sales_order_item.qty_refunded)', '0'),
                'last_sale'              => "MAX(sales_order_item.created_at)",
                'order_count'            => 'COUNT(distinct sales_order_item.order_id)',

                'subtotal_refunded'  => $connection->getIfNullSql('SUM(sales_order_item.base_amount_refunded)', '0'),
                //'total_refunded'    => $connection->getIfNullSql('SUM(main_table.base_total_refunded)', '0')
                'total_refund_items' => $connection->getIfNullSql('SUM(sales_order_item.qty_refunded)', '0')
            ]);

        return $this;
    }

    protected function joinTransactionCountTable() {
        $connection            = $this->getResource()->getConnection();
        $transactionCollection = $this->retailTransactionCollectionFactory->create();
        $transactionCollection->addAttributeToSelect('order_id');
        $transactionCollection->getSelect()->group('order_id');
        $transactionCollection->getSelect()->columns(
            [
                'transaction_count' => 'COUNT(order_id)',
                '0']);
        $select = $this->getSelect();
        $select->joinLeft(
            ['stransaction' => $transactionCollection->getSelec()],
            'stransaction.order_id = main_table.entity_id',
            ['transaction_count' => 'COUNT(stransaction.transaction_count)']);
    }

    protected function joinOrderItemTable() {
        $connection = $this->getResource()->getConnection();

        $totalCostCollection = $this->orderItemCollectionFactory->create();
        $totalCostCollection->addAttributeToSelect('order_id');
        $totalCostCollection->addAttributeToFilter('product_type', ['simple', 'virtual', 'configurable', 'bundle']);

        $totalCostCollection->getSelect()->columns(
            [
                'total_order_cost'     => $connection->getIfNullSql('SUM(base_cost * qty_ordered)', '0'),
                'refunded_items_count' => $connection->getIfNullSql('SUM(qty_refunded)', '0')]);
        $totalCostCollection->getSelect()->group('order_id');

        $select = $this->getSelect();
        $select->joinLeft(
            ['items_order' => $totalCostCollection->getSelect()],
            'items_order.order_id = main_table.entity_id',
            [
                'total_cost'         => $connection->getIfNullSql('SUM(items_order.total_order_cost)', '0'),
                'total_refund_items' => $connection->getIfNullSql('SUM(items_order.refunded_items_count)', '0')]);

        // truong hop get data theo report type thong thuong

        //$select = $this->getSelect();
        //$select->joinLeft(
        //    ['items_order' => $this->getTable('sales_order_item')],
        //    "items_order.order_id = main_table.entity_id ",
        //    [
        //        'total_cost'         => $connection->getIfNullSql('SUM(items_order.qty_ordered * items_order.base_cost)', '0'),
        //        'total_refund_items' => $connection->getIfNullSql('SUM(items_order.qty_refunded)', '0')]);
        //$this->addFieldToFilter('items_order.product_type', ['simple','virtual', 'configurable','bundle']);
        //$this->addFieldToFilter('items_order.parent_item_id', ['null' => true]);
        return $this;
    }

    public function getItemDetailShippingAmount($data, $dateStart, $dateEnd) {
        $typeReport = $data['type'];
        $itemFilter = $data->getData('item_filter');

        $this->setMainTable('sales_order');
        $connection = $this->getResource()->getConnection();
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);

        $this->reportHelper->addDateRangerFilter($this, $dateStart, $dateEnd);
        $this->addFieldToFilter('retail_status', [['nin' => [1, 2, 3]], ['null' => true]]);
        $this->addFieldToFilter('base_total_invoiced', ['neq' => 'NULL']);

        switch ($typeReport) {
            case "customer":
                $this->addFieldToFilter('customer_id', $itemFilter);
                $this->getSelect()->group('customer_id');
                break;
            case "register":
                $this->addFieldToFilter('register_id', $itemFilter);
                $this->getSelect()->group('register_id');
                break;
        }
        $this->getSelect()->columns(
            [
                'shipping_amount'     => $connection->getIfNullSql('SUM(main_table.base_shipping_amount)', '0'),
                'shipping_tax_amount' => $connection->getIfNullSql('SUM(main_table.base_shipping_tax_amount)', '0')
            ]);

        return $this;
    }
}
