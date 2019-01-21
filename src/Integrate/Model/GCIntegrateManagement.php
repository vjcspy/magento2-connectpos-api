<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 10/13/17
 * Time: 3:02 PM
 */

namespace SM\Integrate\Model;

use Magento\Framework\ObjectManagerInterface;
use SM\XRetail\Repositories\Contract\ServiceAbstract;
use Magento\Framework\App\Config\ScopeConfigInterface;

class GCIntegrateManagement extends ServiceAbstract {

    /**
     * @var RPIntegrateInterface
     */
    protected $_currentIntegrateModel;

    /**
     * @var array
     */
    static $LIST_GC_INTEGRATE
        = [
            'aheadWorks' => [
                [
                    "version" => "~1.2.1",
                    "class"   => "SM\\Integrate\\GiftCard\\AheadWorks121"
                ]
            ],
            'mage2_ee'   => [
                [
                    "version" => "~2.1.7",
                    "class"   => "SM\\Integrate\\GiftCard\\Magento2EE"
                ]
            ],
        ];
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * RPIntegrateManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface            $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                      $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\ObjectManagerInterface          $objectManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig   = $scopeConfig;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return \SM\Integrate\GiftCard\Contract\GCIntegrateInterface
     */
    public function getCurrentIntegrateModel() {
        $configIntegrateGiftCardValue = $this->scopeConfig->getValue('xretail/pos/integrate_gc');
        if (is_null($this->_currentIntegrateModel) && $configIntegrateGiftCardValue != 'none') {
            // FIXME: do something to get current integrate class
            $class = self::$LIST_GC_INTEGRATE[$configIntegrateGiftCardValue][0]['class'];

            $this->_currentIntegrateModel = $this->objectManager->create($class);
        }

        return $this->_currentIntegrateModel;
    }

    /**
     * @param $data
     *
     * @return void
     */
    public function saveGCDataBeforeQuoteCollect($data) {
        $this->getCurrentIntegrateModel()->saveGCDataBeforeQuoteCollect($data);
    }

    public function updateRefundToGCProduct($data) {
        if ($m = $this->getCurrentIntegrateModel()) {
            $m->updateRefundToGCProduct($data);
        }
    }

    public function getRefundToGCProductId() {
        return $this->getCurrentIntegrateModel()->getRefundToGCProductId();
    }

    public function getGCCodePool() {
        if ($m = $this->getCurrentIntegrateModel()) {
            $m->getGCCodePool();
        }
        else {
            return [];
        }
    }

    public function getQuoteGCData() {
        //return $this->getCurrentIntegrateModel()->getQuoteGCData()->getOutput();
        return $this->getCurrentIntegrateModel()->getQuoteGCData();
    }
}
