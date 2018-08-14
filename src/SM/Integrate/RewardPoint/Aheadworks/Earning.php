<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 7/28/17
 * Time: 10:36 AM
 */

namespace SM\Integrate\RewardPoint\Aheadworks;


use Magento\Framework\ObjectManagerInterface;

class Earning {

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager) {
        $this->objectManager = $objectManager;
    }

    public function calculation(\Magento\Quote\Model\Quote $quote, $customerId, $websiteId = null) {
        $baseSubTotal     = 0;
        $shippingDiscount = $quote->getBaseShippingDiscountAmount()
                            + $quote->getBaseAwRewardPointsShippingAmount();

        switch ($this->getConfigAheadworks()->getPointsEarningCalculation($websiteId)) {
            case \Aheadworks\RewardPoints\Model\Source\Calculation\PointsEarning::BEFORE_TAX:
                $baseSubTotal = $quote->getBaseGrandTotal()
                                - $quote->getBaseShippingAmount()
                                + $shippingDiscount
                                - $quote->getBaseTaxAmount();
                break;
            case \Aheadworks\RewardPoints\Model\Source\Calculation\PointsEarning::AFTER_TAX:
                $baseSubTotal = $quote->getBaseGrandTotal()
                                - $quote->getBaseShippingAmount()
                                + $shippingDiscount;
                break;
        }
        if ($baseSubTotal <= 0) {
            return 0;
        }

        return $this->getRateCalculator()->calculateEarnPoints($customerId, $baseSubTotal, $websiteId);
    }

    /**
     * @return \Aheadworks\RewardPoints\Model\Calculator\RateCalculator
     */
    public function getRateCalculator() {
        return $this->objectManager->get('Aheadworks\RewardPoints\Model\Calculator\RateCalculator');
    }

    /**
     * @return \Aheadworks\RewardPoints\Model\Config
     */
    protected function getConfigAheadworks() {
        return $this->objectManager->get('Aheadworks\RewardPoints\Model\Config');
    }
}