<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 20/06/2016
 * Time: 11:35
 */

namespace SM\XRetail\Repositories\Contract;


use SM\Core\Api\SearchResult;
use SM\Core\Model\DataObject;

class ServiceAbstract {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var DataObject
     */
    protected $_searchCriteria;
    /**
     * @var \SM\XRetail\Helper\DataConfig
     */
    protected $_dataConfig;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \SM\Core\Api\SearchResult
     */
    protected $searchResult;

    protected $_requestData;

    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->request      = $requestInterface;
        $this->_dataConfig  = $dataConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return \SM\Core\Api\SearchResult
     */
    public function getSearchResult() {
        if (is_null($this->searchResult))
            $this->searchResult = new SearchResult();

        return $this->searchResult;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Retrieve search criteria as DataObject
     *
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     */
    public function getSearchCriteria() {
        if (is_null($this->_searchCriteria))
            if (is_null($this->getRequest()->getParam('searchCriteria')))
                throw new \Exception('Not found field: searchCriteria');
            else
                $this->_searchCriteria = new \Magento\Framework\DataObject($this->getRequest()->getParam('searchCriteria'));

        return $this->_searchCriteria;
    }

    /**
     * @return \SM\XRetail\Helper\DataConfig
     */
    public function getDataConfig() {
        return $this->_dataConfig;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager() {
        return $this->storeManager;
    }

    public function getRequestData() {
        if (is_null($this->_requestData)) {
            $this->_requestData = new DataObject($this->getRequest()->getParams());
        }

        return $this->_requestData;
    }

}