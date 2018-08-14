<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 20/06/2016
 * Time: 14:32
 */

namespace SM\Core\Api;


use SM\Core\Api\Data\Contract\ApiDataAbstract;
use SM\Core\Model\DataObject;

class SearchResult extends DataObject {

    const TYPE_OUTPUT = 'underscore';
    protected $_cacheUnderScore = [];

    public function __construct(
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return mixed
     */
    public function getItems() {
        return $this->getData('items');
    }

    /**
     * @param array $items
     *
     * @return $this
     */
    public function setItems(array $items) {
        $this->setData('items', $items);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroupDataReport() {
        return $this->getData('group_data_report');
    }

    /**
     * @param array $items
     *
     * @return $this
     */
    public function setGroupDataReport(array $items) {
        $this->setData('group_data_report', $items);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateRangerReport() {
        return $this->getData('date_ranger_report');
    }

    /**
     * @param array $items
     *
     * @return $this
     */
    public function setDateRangerReport(array $items) {
        $this->setData('date_ranger_report', $items);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBaseCurrency() {
        return $this->getData('base_currency');
    }

    /**
     * @param array $items
     *
     * @return $this
     */
    public function setBaseCurrency($currency) {
        $this->setData('base_currency', $currency);

        return $this;
    }


    /**
     * @return mixed
     */
    public function getMessageError() {
        return $this->getData('message_error');
    }

    /**
     * @param $messageError
     *
     * @return $this
     */
    public function setMessageError($messageError) {
        $this->setData('message_error', $messageError);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalCount() {
        return $this->getData('total_count');
    }

    /**
     * @param $totalCount
     *
     * @return $this
     */
    public function setTotalCount($totalCount) {
        $this->setData('total_count', $totalCount);

        return $this;
    }

    /**
     * @param $totalPage
     *
     * @return $this
     */
    public function setLastPageNumber($totalPage) {
        $this->setData('last_page_number', $totalPage);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastPageNumber() {
        return $this->getData('last_page_number');
    }

    /**
     * Get Client Cache Key
     *
     * @return string|null
     */
    public function getClientKey() {
        return $this->getData('client_key');
    }

    /**
     * Set Client Cache Key
     *
     * @param string $clientKey
     *
     * @return $this
     */
    public function setClientKey($clientKey) {
        $this->setData('client_key', $clientKey);

        return $this;
    }


    /**
     * @return mixed
     */
    public function getSearchCriteria() {
        return $this->getData('search_criteria');
    }

    /**
     * @param $searchCriteria
     *
     * @return $this
     */
    public function setSearchCriteria($searchCriteria) {
        $this->setData('search_criteria', $searchCriteria->getData());

        return $this;
    }

    /**
     * @param $isLoadFromCache
     *
     * @return $this
     */
    public function setIsLoadFromCache($isLoadFromCache) {
        $this->setData('is_load_from_cache', $isLoadFromCache);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsLoadFromCache() {
        return $this->getData('is_load_from_cache');
    }

    /**
     * @return array
     */
    public function getOutputReport($item_detail) {
        $items = [];

        foreach ($this->getItems() as $item) {
            if ($item instanceof ApiDataAbstract)
                /* @var $item ApiDataAbstract */
                $items[] = $item->getOutput();
            else
                $items[] = $item;
        }
        $datas = [];
        foreach ($this->getGroupDataReport() as $data) {
            $datas[] = $data;
        }

        return $this->formatDataOutput(
            [
                'item_detail'   => $item_detail,
                'items'         => $items,
                'group_data'    => $datas,
                'base_currency' => $this->getBaseCurrency(),
                'date_ranger'   => $this->getDateRangerReport()
            ]);
    }

    /**
     * @return array
     */
    public function getOutput() {
        $items = [];

        foreach ($this->getItems() as $item) {
            if ($item instanceof ApiDataAbstract)
                /* @var $item ApiDataAbstract */
                $items[] = $item->getOutput();
            else
                $items[] = $item;
        }

        return $this->formatDataOutput(
            [
                'items'              => $items,
                'search_criteria'    => $this->getSearchCriteria(),
                'total_count'        => $this->getTotalCount(),
                'message_error'      => $this->getMessageError(),
                'last_page_number'   => $this->getLastPageNumber(),
                'cache_time'         => $this->getCacheTime(),
                'is_load_from_cache' => $this->getIsLoadFromCache() === true,
                'api_version'        => \SM\XRetail\Helper\Data::$API_VERSION
            ]);
    }

    /**
     * @param $time
     *
     * @return $this
     */
    public function setCacheTime($time) {
        $this->setData('cache_time', $time);

        return $this;
    }

    /**
     * @return int|mixed
     */
    public function getCacheTime() {
        return is_null($this->getData('cache_time')) ? intval(microtime(true) * 1000) : intval($this->getData('cache_time'));
    }

    /**
     * @param $array
     *
     * @return mixed
     */
    public function formatDataOutput($array) {
        if (self::TYPE_OUTPUT == 'underscore' && is_array($array))
            return $this->fixArrayKey($array);
        else
            return $array;
    }

    /**
     * @param $arr
     *
     * @return array
     */
    protected function fixArrayKey($arr) {
        $php53 = $this;
        $arr   = array_combine(
            array_map(
                function ($str) use ($php53) {
                    return $php53->_underscore($str);
                },
                array_keys($arr)),
            array_values($arr));
        foreach ($arr as $key => $val) {
            if (is_array($val))
                $this->fixArrayKey($arr[$key]);
        }

        return $arr;
    }

    /**
     * Converts field names for setters and geters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unneccessary preg_replace
     *
     * @param string $name
     *
     * @return string
     */
    public function _underscore($name) {
        if (!isset($this->_cacheUnderScore[$name]))
            $this->_cacheUnderScore[$name] = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));

        return $this->_cacheUnderScore[$name];
    }
}
