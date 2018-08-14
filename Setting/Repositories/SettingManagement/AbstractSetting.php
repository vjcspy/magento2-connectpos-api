<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 08/11/2016
 * Time: 15:35
 */

namespace SM\Setting\Repositories\SettingManagement;


/**
 * Class AbstractSetting
 *
 * @package SM\Setting\Repositories\SettingManagement
 */
abstract class AbstractSetting {

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var
     */
    protected $store;

    /**
     * AbstractSetting constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function build() {
        return [];
    }

    /**
     * @var string
     */
    protected $CODE = "default";

    /**
     * @return string
     */
    public function getCODE() {
        return $this->CODE;
    }

    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig() {
        return $this->scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getStore() {
        return $this->store;
    }

    /**
     * @param mixed $store
     */
    public function setStore($store) {
        $this->store = $store;
    }


}