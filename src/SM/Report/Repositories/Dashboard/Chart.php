<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/20/17
 * Time: 11:26 AM
 */

namespace SM\Report\Repositories\Dashboard;


use Magento\Framework\ObjectManagerInterface;

/**
 * @property \Magento\Framework\ObjectManagerInterface objectManager
 */
class Chart extends \Magento\Backend\Block\Dashboard\Graph {

    const PERIOD_DAY   = '24h';
    const PERIOD_WEEK  = '7d';
    const PERIOD_MONTH = '1m';

    const SCOPE_CHART_OUTLET  = 'outlet';
    const SCOPE_CHART_WEBSITE = 'website';
    const SCOPE_CHART_STORE   = 'store';
    const SCOPE_CHART_REGION  = 'region';

    protected $_scopeChart;
    protected $_period;

    /**
     * @param \Magento\Backend\Block\Template\Context                      $context
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Dashboard\Data                       $dashboardData
     * @param \SM\Report\Helper\Order                                      $dataHelper
     * @param \SM\Report\Model\ResourceModel\Order\CollectionFactory       $orderReportCollectionFactory
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Dashboard\Data $dashboardData,
        ObjectManagerInterface $objectManager,
        \SM\Report\Model\ResourceModel\Order\CollectionFactory $orderReportCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $collectionFactory, $dashboardData, $data);
        $this->_collectionFactory = $orderReportCollectionFactory;
        $this->objectManager      = $objectManager;
    }

    /**
     * @param array $axisMaps
     *
     * @return $this
     */
    public function setAxisMaps($axisMaps) {
        $this->_axisMaps = $axisMaps;

        return $this;
    }


    /**
     * @param $period
     *
     * @return array
     */
    public function getChartData($period, $isGetDate = false) {
        $this->getDataHelper()->setParam('period', $period);

        $timezoneLocal = $this->_localeDate->getConfigTimezone();

        /** @var \DateTime $dateStart */
        /** @var \DateTime $dateEnd */
        list($dateStart, $dateEnd) = $this->_collectionFactory->create()->getDateRange(
            $this->getDataHelper()->getParam('period'),
            $this->getDataHelper()->getParam('start_date'),
            $this->getDataHelper()->getParam('end_date'),
            true
        );
        //$dateStart->setTimezone(new \DateTimeZone($timezoneLocal));
        //$dateEnd->setTimezone(new \DateTimeZone($timezoneLocal));
        if ($this->getDataHelper()->getParam('period') == '24h') {
            $dateEnd->modify('-1 hour');
        }
        else {
            $dateEnd->setTime(23, 59, 59);
            $dateStart->setTime(0, 0, 0);
        }

        $dates     = [];
        $datas     = [];
        $cloneDate = clone  $dateStart;
        while ($dateStart < $dateEnd) {
            switch ($this->getDataHelper()->getParam('period')) {
                case '7d':
                    $d = $d_end = $dateStart->format('Y-m-d');
                    $dateStart->modify('+1 day');
                    break;
                case '6w':
                    if ($dateStart == $cloneDate) {
                        $d     = $dateStart->format('Y-m-d');
                        $d_end = $dateStart->modify('+7 days  -1 second')->format('Y-m-d');
                    }
                    else {
                        $d     = $dateStart->modify('+1 day')->format('Y-m-d');
                        $d_end = $dateStart->modify('+6 days')->format('Y-m-d');
                    }
                    break;
                case '6m':
                    $d     = $dateStart->format('Y-m');
                    $d_end = $dateStart->modify('+1 month')->format('Y-m');
                    break;
                case '1y':
                case '2y':
                    $d = $dateStart->format('Y-m');
                    $dateStart->modify('+1 month');
                    break;
                default:
                    $d = $dateStart->format('Y-m-d H:00');
                    $dateStart->modify('+1 hour');
            }
            // refactor date filter display
            $dates[] = [
                'date_start' => $d,
                'date_end'   => $d_end];
        }
        if ($this->getDataHelper()->getParam('period') == "6w") {
            $this->_allSeries = $this->getCustomizePeriodRowData($this->_dataRows, $dates);
        }
        else {
            //  lấy tất cả data của dataRows
            $this->_allSeries = $this->getRowsData($this->_dataRows);
        }
        if (count($this->_allSeries) == 0) {
            foreach ($this->_dataRows as $rowName) {
                $this->_allSeries[$rowName] = [];
            }
        }
        // lấy theo data của các trục
        foreach ($this->_axisMaps as $axis => $attr) {
            if (in_array($this->getDataHelper()->getParam('period'), ['6w'])) {
                $this->setAxisLabels($axis, $this->getCustomizePeriodRowData($attr, $dates, true));
            }
            else {
                $this->setAxisLabels($axis, $this->getRowsData($attr, true));
            }
        }
        foreach ($dates as $d) {
            foreach ($this->getAllSeries() as $index => $serie) {
                if ($index == 'list_customer') {
                }else { // Vì các axis và allSeries đều lấy từ một nguồn nên sẽ có thứ tự giống nhau, func aray_shift cắt phần từ đầu tiên.
                    if (in_array($d['date_start'], $this->_axisLabels['x'])) {
                        $datas[$index][] = (double)array_shift($this->_allSeries[$index]);
                    }
                    else {
                        $datas[$index][] = 0;
                    }
                }
            }
        }
        /**
         * setting skip step
         */
        if (count($dates) > 8 && count($dates) < 15) {
            $c = 1;
        }
        else {
            if (count($dates) >= 15) {
                $c = 2;
            }
            else {
                $c = 0;
            }
        }
        /**
         * skipping some x labels for good reading
         */
        $i = 0;
        foreach ($dates as $k => $d) {
            if ($i == $c) {
                $dates[$k] = $d;
                $i         = 0;
            }
            else {
                $dates[$k] = '';
                $i++;
            }
        }

        $this->_axisLabels['x'] = $dates;
        $this->_allSeries       = $datas;

        $idx = 'x';
        //foreach ($this->_axisLabels[$idx] as $_index => $_label) {
        //    if ($_label != '') {
        //        $period = new \DateTime($_label, new \DateTimeZone($timezoneLocal));
        //        switch ($this->getDataHelper()->getParam('period')) {
        //            case '24h':
        //                $this->_axisLabels[$idx][$_index] = $this->_localeDate->formatDateTime(
        //                    $period->setTime($period->format('H'), 0, 0),
        //                    \IntlDateFormatter::NONE,
        //                    \IntlDateFormatter::SHORT
        //                );
        //                break;
        //            case '7d':
        //            case '1m':
        //                $this->_axisLabels[$idx][$_index] = $this->_localeDate->formatDateTime(
        //                    $period,
        //                    \IntlDateFormatter::SHORT,
        //                    \IntlDateFormatter::NONE
        //                );
        //                break;
        //            case '1y':
        //            case '2y':
        //                $this->_axisLabels[$idx][$_index] = date('m/Y', strtotime($_label));
        //                break;
        //        }
        //    }
        //    else {
        //        $this->_axisLabels[$idx][$_index] = '';
        //    }
        //}

        if ($isGetDate) {
            return $this->_axisLabels[$idx];
        }

        return $this->initDataForNgChart($this->_axisLabels[$idx], $this->getAllSeries());
    }


    /**
     * @param $axisLabels
     * @param $allSeries
     *
     * @return array
     */
    protected function initDataForNgChart($axisLabels, $allSeries) {
        $chartData = [];

        foreach ((array)$allSeries as $name => $series) {
            $dataLabel    = [];
            $dataValue    = [];
            $currentValue = 0;
            foreach ($axisLabels as $i => $x) {
                if ($x == '') {
                    $currentValue += $series[$i];
                }
                else {
                    $currentValue += $series[$i];
                    $dataLabel[]  = $x;
                    $dataValue[]  = $currentValue;
                    $currentValue = 0;
                }
                //$chartData[$name] = [
                //    'label' => $dataLabel,
                //    'value' => $dataValue
                //];
                $chartData[$name] = $dataValue;
            }
        }

        return $chartData;
    }

    /**
     * Get data helper
     *
     * @return  \SM\Report\Helper\Order
     */
    public function getDataHelper() {
        if (is_null($this->_dataHelper)) {
            $this->_dataHelper = $this->objectManager->create('SM\Report\Helper\Order');
        }

        return $this->_dataHelper;
    }

    /**
     * Get rows data
     *
     * @param array $attributes
     * @param bool  $single
     *
     * @return array
     */
    protected function getCustomizePeriodRowData($attributes, $dates, $single = false) {
        $items = $this->getCollection()->getItems();
        $options = [];
        foreach ($dates as $date) {
            $dateStart = $date['date_start'];
            $dateEnd   = $date['date_end'];
            foreach ($items as $item) {
                if ($single) {
                    if (strtotime($dateStart) <= strtotime($item->getData('range')) && strtotime($item->getData('range')) <= strtotime($dateEnd)) {
                        if ($attributes == "range") {
                            $options[$dateStart] = $dateStart;
                        }else {
                            if (!isset($options[$dateStart])) {
                                $options[$dateStart] = floatval($item->getData($attributes));
                            }else {
                                $options[$dateStart] = $options[$dateStart] + doubleval($item->getData($attributes));
                            }
                        }
                    }
                }else {
                    foreach ((array)$attributes as $attr) {
                        if (strtotime($dateStart) <= strtotime($item->getData('range'))
                            && strtotime($item->getData('range')) <= strtotime(
                                $dateEnd)) {
                            if (!isset($options[$attr][$dateStart])) {
                                if ($attr == "customer_count") {
                                    $options['list_customer'][$dateStart] = $item->getData('list_customer');
                                    $listCustomer                         = explode(",", $options['list_customer'][$dateStart]);
                                    $options[$attr][$dateStart]           = count($listCustomer);
                                }
                                else {
                                    $options[$attr][$dateStart] = $item->getData($attr);
                                }
                            }
                            else {
                                if ($attr == "customer_count") {
                                    $options['list_customer'][$dateStart] = $options['list_customer'][$dateStart] . "," . $item->getData(
                                            'list_customer');
                                    $listCustomer                         = array_unique(explode(",", $options['list_customer'][$dateStart]));
                                    $options[$attr][$dateStart]           = count($listCustomer);
                                }
                                else {
                                    $options[$attr][$dateStart] = doubleval($options[$attr][$dateStart]) + doubleval($item->getData($attr));
                                }
                            }
                        }
                    }
                }
            }
        }
        return $options;
    }
}