<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 20/03/2017
 * Time: 17:23
 */

namespace SM\Integrate\RewardPoint;


use Magento\Framework\Exception\NoSuchEntityException;
use SM\Integrate\Data\RewardPointQuoteData;
use SM\Integrate\RewardPoint\Contract\AbstractRPIntegrate;
use SM\Integrate\RewardPoint\Contract\RPIntegrateInterface;

/**
 * Class AheadWorks1
 *
 * @package SM\Integrate\RewardPoint
 */
class AheadWorks100 extends AbstractRPIntegrate implements RPIntegrateInterface {

    /**
     * @var \Aheadworks\RewardPoints\Model\Calculator\SpendAmountCalculator
     */
    protected $spendAmountCalculator;
    /**
     * @var \Aheadworks\RewardPoints\Model\Transaction
     */
    protected $transactionFactory;
    /**
     * @var \Aheadworks\RewardPoints\Api\CuCustomerRewardPointsManagementInterface
     */
    protected $customerRewardPointService;
    /**
     * @var \Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface
     */
    protected $AHCustomerRPManagement;
    /**
     * @var \SM\Integrate\RewardPoint\Aheadworks\Earning
     */
    private $earningCalculator;

    /**
     * AheadWorks100 constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \SM\Integrate\RewardPoint\Aheadworks\Earning $earning
    ) {
        $this->earningCalculator = $earning;
        parent::__construct($objectManager);
    }


    /**
     * @param $data
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    function saveRPDataBeforeQuoteCollect($data) {

        if (isset($data['use_reward_point']) && $data['use_reward_point'] == true) {
            /** @var  \Magento\Quote\Model\Quote $quote */
            $quote = $this->getQuote();

            if (!$quote->getCustomerId()
                || !$this->getCustomerRewardPointService()
                         ->getCustomerRewardPointsBalance($quote->getCustomerId())
            ) {
                throw new NoSuchEntityException(__('No reward points to be used'));
            }

            $quote->getShippingAddress()->setCollectShippingRates(true);

            $quote->setAwUseRewardPoints(true);
        }
    }

    /**
     * @return \Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface
     */
    protected function getCustomerRewardPointService() {
        if (is_null($this->customerRewardPointService)) {
            $this->customerRewardPointService = $this->objectManager->get('Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface');
        }

        return $this->customerRewardPointService;
    }

    /**
     * @return \Aheadworks\RewardPoints\Model\Calculator\SpendAmountCalculator
     */
    protected function getSpendAmountCalculator() {
        if (is_null($this->spendAmountCalculator)) {
            $this->spendAmountCalculator = $this->objectManager->get('Aheadworks\RewardPoints\Model\Calculator\SpendAmountCalculator');
        }

        return $this->spendAmountCalculator;
    }

    /**
     * @return RewardPointQuoteData
     */
    function getQuoteRPData() {
        // TODO: Implement getRPDataAfterQuoteCollect() method.
        $customerRPDetail = $this->getAWCustomerRPManagement()->getCustomerRewardPointsDetails($this->getQuote()->getCustomerId());
        $quoteRpData      = new RewardPointQuoteData();
        $quoteRpData->addData(
            [
                'use_reward_point'                  => $this->getQuote()->getAwUseRewardPoints(),
                'customer_balance'                  => $customerRPDetail->getCustomerRewardPointsBalance(),
                'customer_balance_currency'         => $customerRPDetail->getCustomerRewardPointsBalanceCurrency(),
                'customer_balance_base_currency'    => $customerRPDetail->getCustomerRewardPointsBalanceBaseCurrency(),
                'reward_point_spent'                => $this->getQuote()->getData('aw_reward_points'),
                'reward_point_discount_amount'      => $this->getQuote()->getData('aw_reward_points_amount'),
                'base_reward_point_discount_amount' => $this->getQuote()->getData('base_aw_reward_points_amount'),
                'reward_point_earn'                 => $this->earningCalculator->calculation($this->getQuote(), $this->getQuote()->getCustomerId()),
                'customer_reward_points_once_min_balance' => $customerRPDetail->getCustomerRewardPointsOnceMinBalance()
            ]);
        return $quoteRpData;
    }

    /**
     * @return \Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface
     */
    protected function getAWCustomerRPManagement() {
        if (is_null($this->AHCustomerRPManagement)) {
            $this->AHCustomerRPManagement = $this->objectManager->get('Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface');
        }

        return $this->AHCustomerRPManagement;
    }

    /**
     * @param      $customerId
     * @param null $scope
     *
     * @return int
     */
    function getCurrentPointBalance($customerId, $scope = null) {
        return $this->getCustomerRewardPointService()->getCustomerRewardPointsBalance($customerId, $scope);
    }

    function getTransactionFactory(){
        if(is_Null($this->transactionFactory)){
            $this->transactionFactory = $this->objectManager->get('Aheadworks\RewardPoints\Model\Transaction');
        }
        return $this->transactionFactory;
    }


    function getTransactionByOrder($id){
        return $this->getTransactionFactory()->load(intval($id))->getBalance();
    }
}