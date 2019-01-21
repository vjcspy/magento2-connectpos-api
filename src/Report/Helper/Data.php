<?php

namespace SM\Report\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use SM\Core\Model\DataObject;

class Data {

    const GROUP_TYPE_DATE = "last,last_from";

    // con first sales va last sales,day_of_week,hour
    const FILTER_DATA_CAN_COMPARE = "revenue,total_cost,gross_profit,margin,total_tax,grand_total,cart_size,cart_value,cart_value_incl_tax,customer_count,discount_amount,discount_percent,item_sold,order_count,return_percent,return_count,shipping_amount,shipping_tax,shipping_tax_refunded,subtotal_refunded,total_refunded,day_of_week,hour";


    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Order\CollectionFactory
     */
    protected $reportOrderResource;

    /**
     * @var \SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory
     */
    protected $paymentCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface         $timezoneInterface
     * @param \Magento\Framework\ObjectManagerInterface                    $objectManager
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $reportOrderResource
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $reportOrderResource,
        \SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory $paymentCollectionFactory,
        DateTime $date
    ) {
        $this->objectManager            = $objectManager;
        $this->timezoneInterface        = $timezoneInterface;
        $this->scopeConfig              = $scopeConfig;
        $this->reportOrderResource      = $reportOrderResource;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->date                     = $date;
    }

    public function getDateRanger($is_date_compare, $period_data, $total_start_date, $total_end_date , $is_getGroupData = false) {
        $array_date_start = explode('/', $total_start_date);
        $array_date_end   = explode('/', $total_end_date);

        // convert date start , date end theo gio cua local sang GMT de lam cau query select ( vi magento collection su dung mui gio GMT de get data)
        $date_start_GMT = $this->timezoneInterface->date($array_date_start[0], null, false);
        $date_end_GMT   = $this->timezoneInterface->date($array_date_end[0], null, false);

        $date_start             = $this->timezoneInterface->date($array_date_start[1], null, false);
        $date_end               = $this->timezoneInterface->date($array_date_end[1], null, false);

        if($is_getGroupData){
            return [
                    'date_start'             => $date_start->format('Y-m-d H:i:s'),
                    'date_end'               => $date_end->format('Y-m-d H:i:s'),
                    'date_start_GMT' => $date_start_GMT->format('Y-m-d H:i:s'),
                    'date_end_GMT'   => $date_end_GMT->format('Y-m-d H:i:s')];
        }
        if ($is_date_compare && is_array($period_data)) {
            $period_type = $period_data['range_type'];
            $type        = $period_data['type'];
            $count       = $period_data['count'];
            //if ($period_type != "hour") {
            //    $date_end_useTimeZone->setTime(23, 59, 59);
            //    $date_start_useTimeZone->setTime(0, 0, 0);
            //}


            $listTypeDate = explode(",", self::GROUP_TYPE_DATE);
            $datas        = [];
            if (in_array($type, $listTypeDate)) {
                $start_date             = clone $date_start;
                $start_date_GMT = clone $date_start_GMT;
                switch ($period_type) {
                    case "year" :
                        for ($i = 1; $i <= $count; $i++) {
                            $d_start = $start_date->format('Y-m-d 00:00:00');
                            $d_end   = $start_date->modify('+ 1 year - 1 day')->format('Y-m-d 23:59:59');
                            $start_date->modify("+1 day");

                            $d_start_GMT = $start_date_GMT->format('Y-m-d H:i:00');
                            $d_end_GMT   = $start_date_GMT->modify('+ 1 year - 1 minute ')->format('Y-m-d H:i:59');
                            $start_date_GMT->modify("+ 1 minute ");

                            $dateRanger = new DataObject(
                                [
                                    'date_start'             => $d_start,
                                    'date_end'               => $d_end,
                                    'date_start_GMT' => $d_start_GMT,
                                    'date_end_GMT'   => $d_end_GMT
                                ]);
                            $datas[]    = $dateRanger;
                        }
                        break;
                    case "quarter" :
                        for ($i = 1; $i <= $count; $i++) {
                            $d_start = $start_date->format('Y-m-d 00:00:00');
                            $d_end   = $start_date->modify(' + 3 months - 1 day')->format('Y-m-d 23:59:59');
                            $start_date->modify("+1 day");

                            $d_start_GMT = $start_date_GMT->format('Y-m-d H:i:00');
                            $d_end_GMT   = $start_date_GMT->modify('+ 3 months - 1 minute')->format('Y-m-d H:i:59');
                            $start_date_GMT->modify("+1 minute");
                            $dateRanger = new DataObject(
                                [
                                    'date_start'             => $d_start,
                                    'date_end'               => $d_end,
                                    'date_start_GMT' => $d_start_GMT,
                                    'date_end_GMT'   => $d_end_GMT
                                ]);
                            $datas[]    = $dateRanger;
                        }
                        break;
                    case "month" :
                        for ($i = 1; $i <= $count; $i++) {
                            $d_start = $start_date->format('Y-m-d 00:00:00');
                            $d_end   = $start_date->modify(' + 1 month - 1 day')->format('Y-m-d 23:59:59');
                            $start_date->modify("+1 day");

                            $d_start_GMT = $start_date_GMT->format('Y-m-d H:i:00');
                            $d_end_GMT   = $start_date_GMT->modify('+ 1 month - 1 minute')->format('Y-m-d H:i:59');
                            $start_date_GMT->modify("+1 minute");
                            $dateRanger = new DataObject(
                                [
                                    'date_start'             => $d_start,
                                    'date_end'               => $d_end,
                                    'date_start_GMT' => $d_start_GMT,
                                    'date_end_GMT'   => $d_end_GMT
                                ]);
                            $datas[]    = $dateRanger;
                        }
                        break;
                    case "week" :
                        for ($i = 1; $i <= $count; $i++) {
                            $d_start = $start_date->format('Y-m-d 00:00:00');
                            $d_end   = $start_date->modify(' + 7 days - 1 day')->format('Y-m-d 23:59:59');
                            $start_date->modify("+1 day");

                            $d_start_GMT = $start_date_GMT->format('Y-m-d H:i:00');
                            $d_end_GMT   = $start_date_GMT->modify('+ 7 days - 1 minute')->format('Y-m-d H:i:59');
                            $start_date_GMT->modify("+1 minute");
                            $dateRanger = new DataObject(
                                [
                                    'date_start'             => $d_start,
                                    'date_end'               => $d_end,
                                    'date_start_GMT' => $d_start_GMT,
                                    'date_end_GMT'   => $d_end_GMT
                                ]);
                            $datas[]    = $dateRanger;
                        }
                        break;
                    case "day" :
                        for ($i = 1; $i <= $count; $i++) {
                            $d_start = $start_date->format('Y-m-d 00:00:00');
                            $d_end   = $start_date->format('Y-m-d 23:59:59');
                            $start_date->modify("+1 day");

                            $d_start_GMT = $start_date_GMT->format('Y-m-d H:i:00');
                            $d_end_GMT   = $start_date_GMT->modify('+ 1 day - 1 minute')->format('Y-m-d H:i:59');
                            $start_date_GMT->modify("+1 minute");
                            $dateRanger = new DataObject(
                                [
                                    'date_start'             => $d_start,
                                    'date_end'               => $d_end,
                                    'date_start_GMT' => $d_start_GMT,
                                    'date_end_GMT'   => $d_end_GMT
                                ]);
                            $datas[]    = $dateRanger;
                        }
                        break;
                    case "hour" :
                        for ($i = 1; $i <= $count; $i++) {
                            $d_start = $start_date->format("Y-m-d H:i:s");
                            $d_end   = $start_date->modify('+1 hour - 1 minute')->format("Y-m-d H:i:s");
                            $start_date->modify("+1 minute");

                            $d_start_GMT = $start_date_GMT->format("Y-m-d  H:i:s");
                            $d_end_GMT   = $start_date_GMT->modify('+1 hour - 1 minute')->format("Y-m-d H:i:s");
                            $start_date_GMT->modify("+1 minute");

                            $dateRanger = new DataObject(
                                [
                                    'date_start'             => $d_start,
                                    'date_end'               => $d_end,
                                    'date_start_GMT' => $d_start_GMT,
                                    'date_end_GMT'   => $d_end_GMT
                                ]);
                            $datas[]    = $dateRanger;
                        }
                        break;
                }
            }
            else {
                $dateRanger = new DataObject(
                    [
                        'date_start'             => $date_start->format('Y-m-d H:i:s'),
                        'date_end'               => $date_end->format('Y-m-d H:i:s'),
                        'date_start_GMT' => $date_start_GMT->format('Y-m-d H:i:s'),
                        'date_end_GMT'   => $date_end_GMT->format('Y-m-d H:i:s')
                    ]);
                $datas      = [$dateRanger];
            }
        }
        else {
            $dateRanger = new DataObject(
                [
                    'date_start'             => $date_start->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
                    'date_end'               => $date_end->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
                    'date_start_GMT' => $date_start_GMT->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
                    'date_end_GMT'   => $date_end_GMT->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
                    //'date_start' => (new \DateTime())->setTimestamp(strtotime($date_start))->format(
                    //    \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
                    //'date_end'   => (new \DateTime())->setTimestamp(strtotime($date_end))->format(
                    //    \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
                ]);
            $datas      = [$dateRanger];
        }
        return $datas;
    }

    protected function getReportOrderResource() {
        return $this->reportOrderResource->create();
    }

    public function addDateRangerFilter($collection, $dateStart, $dateEnd) {
        $dateRanger = $this->getReportOrderResource()->getDateRange('custom', $dateStart, $dateEnd);

        return $collection->addFieldToFilter('main_table.created_at', $dateRanger);
    }

    public function filterByColumn($collection, $arrayDataFillter, $is_filter_total_value = false) {
        $grand = '/(^>)=?\d*/';
        $less  = '/(^<)=?\d*/';
        $eq    = '/(^=)\d*/';
        if ($arrayDataFillter)
            foreach ($arrayDataFillter as $dataFilter) {
                if ($dataFilter['search_value'] != "") {
                    $searchColumnName = $dataFilter['name'];
                    $searchValue      = $dataFilter['search_value'];

                    $listFilterDataCanCompare = explode(",", self::FILTER_DATA_CAN_COMPARE);
                    // khi search những column có thể compare
                    if (in_array($searchColumnName, $listFilterDataCanCompare)) {
                        if (!is_array($searchValue)) {
                            $searchValue = preg_replace('/\s+/', '', $searchValue);
                            $searchValue = preg_replace('/[&\#,+()$~%":*?{}]/', '', $searchValue);
                            if (preg_match($grand, $searchValue)) {
                                $searchValue = str_replace('>', '', $searchValue);
                                $typeSearch  = '>';
                            }
                            else if (preg_match($less, $searchValue)) {
                                $searchValue = str_replace('<', '', $searchValue);
                                $typeSearch  = '<';
                            }
                            else {
                                $searchValue = str_replace('=', '', $searchValue);
                                $typeSearch  = '=';
                            }
                        } else
                            $typeSearch  = '=';
                        if ($is_filter_total_value) {
                            if ($searchColumnName == 'gross_profit') {
                                $collection->getSelect()->having('ROUND (revenue - total_cost, 2)' . $typeSearch . '?', $searchValue);
                            }
                            elseif ($searchColumnName == 'margin') {
                                $collection->getSelect()->having('ROUND((ROUND(revenue - total_cost, 2) / ROUND(revenue, 2)) *100, 2)' . $typeSearch . '?', $searchValue);
                            }
                            elseif ($searchColumnName == 'cart_value') {
                                $collection->getSelect()->having('ROUND (revenue / order_count, 2) ' . $typeSearch . '?', $searchValue);
                            }
                            elseif ($searchColumnName == 'cart_size') {
                                $collection->getSelect()->having('ROUND ((item_sold + total_refund_items) / order_count, 2) ' . $typeSearch . '?', $searchValue);
                            }
                            elseif ($searchColumnName == 'cart_value_incl_tax') {
                                $collection->getSelect()->having('ROUND (grand_total / order_count, 2)' . $typeSearch . '?', $searchValue);
                            }
                            elseif ($searchColumnName == 'discount_percent') {
                                $collection->getSelect()->orhaving('ROUND ((-discount_amount / (-discount_amount+total_for_discount_percent))*100, 2)' . $typeSearch . '?', $searchValue);
                                $collection->getSelect()->orhaving('ROUND ((discount_amount / (discount_amount+total_for_discount_percent))*100, 2)' . $typeSearch . '?', $searchValue);
                            }
                            elseif ($searchColumnName == 'discount_amount') {
                                $collection->getSelect()->orhaving('ROUND (-discount_amount, 2)' . $typeSearch . '?', $searchValue);
                                $collection->getSelect()->orhaving('ROUND (discount_amount, 2)' . $typeSearch . '?', $searchValue);
                            }
                            elseif ($searchColumnName == 'return_percent') {
                                $collection->getSelect()->having('ROUND ((total_refund_items / (item_sold+total_refund_items))*100, 2) ' . $typeSearch . '?', $searchValue);
                            }
                            elseif ($searchColumnName == 'return_count') {
                                $collection->getSelect()->having('total_refund_items' . $typeSearch . '?', $searchValue);
                            }
                            elseif ($searchColumnName == 'day_of_week') {
                                foreach ($dataFilter['search_value'] as $day)
                                    $collection->getSelect()->orhaving("`day_of_week` " . $typeSearch . ' ?', $day);
                            }
                            elseif ($searchColumnName == 'hour') {
                                foreach ($dataFilter['search_value'] as $day)
                                    $collection->getSelect()->orhaving("`hour` " . $typeSearch . ' ?', $day);
                            }
                            else {
                                $collection->getSelect()->having('ROUND ('.$searchColumnName.', 2)' . $typeSearch . '?', $searchValue);
                            }
                        }
                    }
                    else {
                        if (!is_array($searchValue)) {
                            $searchValue = trim($searchValue);
                            $searchValue = '%' . $searchValue . '%';
                        }
                        $nullValue = '/(no){1}\w*/';
                        switch ($searchColumnName) {
                            case 'customer':
                                $stringReplace = preg_replace('/\s+/', '', $searchValue);
                                $collection->getSelect()
                                           ->where('CONCAT(customer_firstname,"",customer_lastname) LIKE ?', '%' . $stringReplace . '%');
                                break;
                            case 'magento_website':
                                $collection->getSelect()
                                           ->where('`website_name`.`name` LIKE ?', '%' . $searchValue . '%');
                                break;
                            case 'customer_telephone':
                                if (preg_match($nullValue, strtolower($searchValue))) {
                                    $collection->getSelect()
                                               ->where('`cusvarchar`.`value` IS NULL');
                                } else {
                                    $collection->getSelect()
                                               ->where('`cusvarchar`.`value` LIKE ?', '%' . $searchValue . '%');
                                }
                                break;
                            case 'payment_method':
                                $collection->getSelect()
                                           ->where('`spayment`.`method` LIKE ?', '%' . $searchValue . '%');
                                break;
                            case 'register':
                                $collection->getSelect()
                                           ->where('`sregister`.`name` LIKE ?', '%' . $searchValue . '%');
                                break;
                            case 'outlet':
                                $collection->getSelect()
                                           ->where('`sm_outlet`.`name` LIKE ?', '%' . $searchValue . '%');
                                break;
                            case 'manufacturer':
                                if (preg_match($nullValue, strtolower($searchValue))) {
                                    $collection->getSelect()
                                               ->where('`eav_option`.`value` IS NULL');
                                } else {
                                    $collection->getSelect()
                                               ->where('`eav_option`.`value` LIKE ?', '%' . $searchValue . '%');
                                }
                                break;
                            case 'category':
                                $collection->getSelect()
                                           ->where('`category_varchar`.`value` LIKE ?', '%' . $searchValue . '%');
                                break;
                            case 'order_status':
                                if (in_array('null', $dataFilter['search_value'])){
                                    array_pop($dataFilter['search_value']);
                                    $collection->addFieldToFilter(
                                        'retail_status',
                                        [
                                            ['in' => $dataFilter['search_value']],
                                            ['null' => true]
                                        ]);
                                } else
                                    $collection->addAttributeToFilter('retail_status', ['in' => $dataFilter['search_value']]);
                                break;
                            case 'user':
                                $collection->addFieldToFilter('user_id', ['like' => $searchValue]);
                                break;
                            case 'magento_storeview':
                                $collection->addFieldToFilter('store_name', ['like' => $searchValue]);
                                break;
                            case 'customer_group':
                                $collection->addFieldToFilter('customer_group_code', ['like' => $searchValue]);
                                break;
                            case 'currency':
                                $collection->addFieldToFilter('order_currency_code', ['like' => $searchValue]);
                                break;
                            case 'shipping_method':
                                if (preg_match($nullValue, strtolower($searchValue))) {
                                    $collection->getSelect()
                                               ->where('`shipping_description` IS NULL');
                                } else {
                                    $collection->addFieldToFilter('shipping_description', ['like' => $searchValue]);
                                }
                                break;
                            case 'product':
                                $collection->addFieldToFilter('name', ['like' => $searchValue]);
                                break;
                            case 'reference_number':
                                $collection->addFieldToFilter('reference_number', ['like' => $searchValue]);
                                break;
                            case 'region':
                                $collection->getSelect()
                                           ->where('`sm_region`.`region_name` LIKE ?', '%' . $searchValue . '%');
                                break;
                            default:
                                $collection->addFieldToFilter($searchColumnName, ['like' => $searchValue]);
                                break;
                        }
                    }

                }
            }
        return $collection;
    }

    public function getRetailPaymentTitleByType($type) {
        $retail_payment = $this->paymentCollectionFactory->create()->load();
    }

    /**
     * Retrieves global timezone
     *
     * @return string
     */
    public function getTimezone($isMysql = false) {

        $foo = $this->date;
        $foo->calculateOffset(
            $this->scopeConfig->getValue(
                'general/locale/timezone',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ));
        if ($isMysql) {
            $offsetInt = -$foo->getGmtOffset();
            $offset    = ($offsetInt >= 0 ? '+' : '-') . sprintf('%02.0f', round(abs($offsetInt / 3600))) . ':'
                         . (sprintf('%02.0f', abs(round((abs($offsetInt) - round(abs($offsetInt / 3600)) * 3600) / 60))));

            return $offset;
        }
        else {
            return $foo->getGmtOffset();
        }
    }
}
