<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SM\Shipping\Model\Carrier;

use Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use SM\Sales\Repositories\OrderManagement;

/**
 * Flat rate shipping model
 */
class RetailStorePickUp extends AbstractCarrier implements CarrierInterface {

    /**
     * @var string
     */
    protected $_code = 'smstorepickup';
    const METHOD_CODE = 'smstorepickup';

    protected $outletCollectionFactory;

    /**
     * RetailShipping constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array                                                       $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory $outletCollectionFactory,
        array $data = []
    ) {
        $this->_rateResultFactory  = $rateResultFactory;
        $this->_rateMethodFactory  = $rateMethodFactory;
        $this->outletCollectionFactory = $outletCollectionFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }


    /**
     * @return array
     */
    public function getAllowedMethods() {
    $collection = $this->collectionFactory->create();
        if($collection->getSize() == 0){
            return [];
        }else{
            return [$this->_code => $this->getConfigData('name')];
        }
    }

    /**
     * @param RateRequest $request
     *
     * @return Result|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collectRates(RateRequest $request) {
        if (!$this->getConfigFlag('enable')) {
            return false;
        }
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $amount = $this->getConfigData('price');

        $method->setPrice($amount);
        $method->setCost($amount);

        $result->append($method);

        return $result;
    }
}
