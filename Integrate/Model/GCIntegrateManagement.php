<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 10/13/17
 * Time: 3:02 PM
 */

namespace SM\Integrate\Model;

use Magento\Framework\ObjectManagerInterface;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

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
            'ahead_works' => [
                [
                    "version" => "~1.2.1",
                    "class"   => "SM\\Integrate\\GiftCard\\AheadWorks121"
                ]
            ]
        ];
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * RPIntegrateManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface    $requestInterface
     * @param \SM\XRetail\Helper\DataConfig              $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return \SM\Integrate\GiftCard\Contract\GCIntegrateInterface
     */
    public function getCurrentIntegrateModel() {
        if (is_null($this->_currentIntegrateModel)) {
            // FIXME: do something to get current integrate class
            $class = self::$LIST_GC_INTEGRATE['ahead_works'][0]['class'];

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

    public function updateRefundToGCProduct($data){
        $this->getCurrentIntegrateModel()->updateRefundToGCProduct($data);
    }

    public function getRefundToGCProductId() {
      return  $this->getCurrentIntegrateModel()->getRefundToGCProductId();
    }

    public function getGCCodePool(){
        return $this->getCurrentIntegrateModel()->getGCCodePool();
    }

    public function getQuoteGCData() {
        //return $this->getCurrentIntegrateModel()->getQuoteGCData()->getOutput();
        return $this->getCurrentIntegrateModel()->getQuoteGCData();
    }
}