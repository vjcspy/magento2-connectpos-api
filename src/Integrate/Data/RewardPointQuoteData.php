<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 20/03/2017
 * Time: 19:07
 */

namespace SM\Integrate\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class RewardPointQuoteData extends ApiDataAbstract {

    public function getUseRewardPoint() {
        return $this->getData('use_reward_point');
    }

    public function getCustomerBalance() {
        return $this->getData('customer_balance');
    }

    public function getRewardPointSpent() {
        return $this->getData('reward_point_spent');
    }

    public function getRewardPointDiscountAmount() {
        return $this->getData('reward_point_discount_amount');
    }

    public function getBaseRewardPointDiscountAmount() {
        return $this->getData('base_reward_point_discount_amount');
    }

    public function getCustomerBalanceCurrency() {
        return $this->getData('customer_balance_currency');
    }

    public function getCustomerBalanceBaseCurrency() {
        return $this->getData('customer_balance_base_currency');
    }

    public function getRewardPointEarn() {
        return $this->getData('reward_point_earn');
    }

    public function getCustomerRewardPointsOnceMinBalance() {
        return $this->getData('customer_reward_points_once_min_balance');
    }
}