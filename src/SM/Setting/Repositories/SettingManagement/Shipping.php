<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 08/11/2016
 * Time: 16:04
 */

namespace SM\Setting\Repositories\SettingManagement;


class Shipping extends AbstractSetting implements SettingInterface {

    protected $CODE = 'shipping';

    public function build() {
        // TODO: Implement build() method.
        return [
            'country_id' => $this->getScopeConfig()->getValue(
                \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStore()
            ),
            'region_id'  => $this->getScopeConfig()->getValue(
                \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_REGION_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStore()
            ),
            'postcode'   => $this->getScopeConfig()->getValue(
                \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_POSTCODE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStore()
            )
        ];
    }
}