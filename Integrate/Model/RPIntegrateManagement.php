<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 20/03/2017
 * Time: 17:55
 */

namespace SM\Integrate\Model;


use Magento\Framework\ObjectManagerInterface;
use SM\Integrate\RewardPoint\Contract\RPIntegrateInterface;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

/**
 * Class RPIntegrateManagement
 *
 * @package SM\Integrate\Model
 */
class RPIntegrateManagement extends ServiceAbstract {

    /**
     * @var RPIntegrateInterface
     */
    protected $_currentIntegrateModel;

    /**
     * @var array
     */
    static $LIST_RP_INTEGRATE
        = [
            'ahead_works' => [
                [
                    "version" => "~1.0.0",
                    "class"   => "SM\\Integrate\\RewardPoint\\AheadWorks100"
                ]
            ],
            'mage_store'  => [
                [
                    'version' => "~1.0.0",
                    "class"   => "SM\\Integrate\\RewardPoint\\MageStore100"
                ]
            ],
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
     * @return \SM\Integrate\RewardPoint\Contract\RPIntegrateInterface
     */
    public function getCurrentIntegrateModel() {
        if (is_null($this->_currentIntegrateModel)) {
            // FIXME: do something to get current integrate class
            $class = self::$LIST_RP_INTEGRATE['ahead_works'][0]['class'];

            $this->_currentIntegrateModel = $this->objectManager->create($class);
        }

        return $this->_currentIntegrateModel;
    }

    /**
     * @return array
     */
    public function getQuoteRPData() {
        return $this->getCurrentIntegrateModel()->getQuoteRPData()->getOutput();
    }

    /**
     * @param $data
     *
     * @return void
     */
    public function saveRPDataBeforeQuoteCollect($data) {
        $this->getCurrentIntegrateModel()->saveRPDataBeforeQuoteCollect($data);
    }


    /**
     * @param $data
     */
    public function getTransactionByOrder($id){
       return $this->getCurrentIntegrateModel()->getTransactionByOrder($id);
    }
}