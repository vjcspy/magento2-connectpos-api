<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 07/12/2016
 * Time: 09:17
 */

namespace SM\Payment\Model;


class RetailMultiple extends \Magento\Payment\Model\Method\AbstractMethod {

    const PAYMENT_METHOD_RETAILMULTIPLE_CODE = 'retailmultiple';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_RETAILMULTIPLE_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = 'SM\Payment\Block\Form\RetailMultiple';

    /**
     * @var string
     */
    protected $_infoBlockType = 'SM\Payment\Block\Info\RetailMultiple';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * @return string
     */
    public function getPayableTo() {
        return $this->getConfigData('payable_to');
    }

    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\DataObject|mixed $data
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data) {
        $this->getInfoInstance()->setAdditionalInformation('split_data', json_encode($data->getData('additional_data')));

        return $this;
    }
}
