<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 19/12/2016
 * Time: 15:04
 */

namespace SM\DiscountWholeOrder\Observer;


use Magento\Framework\Event\Observer;
use SM\Sales\Repositories\OrderManagement;

/**
 * Class WholeOrderDiscount
 *
 * @package SM\DiscountWholeOrder\Observer
 */
class WholeOrderDiscount implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * WholeOrderDiscount constructor.
     *
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->ruleFactory   = $ruleFactory;
        $this->registry      = $registry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection $collection */
        $collection         = $observer->getData('collection');
        $discountWholeOrder = $this->registry->registry(\SM\Sales\Repositories\OrderManagement::DISCOUNT_WHOLE_ORDER_KEY);

        if ($collection instanceof \Magento\SalesRule\Model\ResourceModel\Rule\Collection) {
            if (!OrderManagement::$IS_COLLECT_RULE) {
                $collection->clear();

                return;
            }
            if ($discountWholeOrder) {
                if (!isset($discountWholeOrder['isPercentMode']))
                    throw new \Exception("Can't get type discount whole order");

                if ($discountWholeOrder['isPercentMode'] == true)
                    $rule = $this->getRule()->addData($this->getRulePercentData($discountWholeOrder));
                else
                    $rule = $this->getRule()->addData($this->getRuleFixAmountData($discountWholeOrder));

                //$collection->clear();
                $collection->addItem($rule);
            }
        }
    }

    /**
     * @param $data
     *
     * @return array
     */
    private function getRulePercentData($data) {
        return $this->objectManager->create('SM\DiscountWholeOrder\Observer\WholeOrderDiscount\PercentOfProductPriceDiscount')->getRule($data);
    }

    /**
     * @param $data
     *
     * @return array
     */
    private function getRuleFixAmountData($data) {
        return $this->objectManager->create('SM\DiscountWholeOrder\Observer\WholeOrderDiscount\FixAmountDiscountForWholeCart')->getRule($data);
    }

    /**
     * @return \Magento\SalesRule\Model\Rule
     */
    public function getRule() {
        return $this->ruleFactory->create();
    }
}