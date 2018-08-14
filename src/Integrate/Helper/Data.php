<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 20/03/2017
 * Time: 18:51
 */

namespace SM\Integrate\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\ObjectManagerInterface;


/**
 * Class Data
 *
 * @package SM\Integrate\Helper
 */
class Data {

    private $_isIntegrateRp;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private   $scopeConfig;
    private   $_isIntegrateGc;
    protected $_isIntegrateWh;
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface          $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Module\ModuleListInterface      $moduleList
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        ModuleListInterface $moduleList
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig   = $scopeConfig;
        $this->moduleList    = $moduleList;
    }

    /**
     * @return bool
     */
    public function isIntegrateRP() {
        if (is_null($this->_isIntegrateRp)) {
            $configValue          = $this->scopeConfig->getValue('xretail/pos/integrate_rp');
            $this->_isIntegrateRp = !!$configValue && $configValue !== 'none';
        }

        return $this->_isIntegrateRp;
    }

    /**
     * @return bool
     */
    public function isIntegrateGC() {
        if (is_null($this->_isIntegrateGc)) {
            $configValue          = $this->scopeConfig->getValue('xretail/pos/integrate_gc');
            $this->_isIntegrateGc = !!$configValue && $configValue !== 'none';
        }

        return $this->_isIntegrateGc;
    }

    /**
     * @return bool
     */
    public function isIntegrateWH() {
        return !!$this->moduleList->getOne("BoostMyShop_AdvancedStock");
    }

    public function isAHWGiftCardxist(){
        return !!$this->moduleList->getOne("Aheadworks_Giftcard");
    }

    public function isAHWRewardPoints() {
        return !!$this->moduleList->getOne("Aheadworks_RewardPoints");
    }

    /**
     * @return \SM\Integrate\Model\RPIntegrateManagement
     */
    public function getRpIntegrateManagement() {
        return $this->objectManager->get('SM\Integrate\Model\RPIntegrateManagement');
    }

    public function getGcIntegrateManagement(){
        return $this->objectManager->get('SM\Integrate\Model\GCIntegrateManagement');
    }

    /**
     * @return \SM\Integrate\Model\WarehouseIntegrateManagement
     */
    public function getWarehouseIntegrateManagement() {
        return $this->objectManager->get('SM\Integrate\Model\WarehouseIntegrateManagement');
    }
}
