<?php

namespace SM\Payment\Model;

class RetailPayment extends \Magento\Framework\Model\AbstractModel
    implements RetailPaymentInterface, \Magento\Framework\DataObject\IdentityInterface {

    const CACHE_TAG                 = 'sm_payment';
    const GIFT_CARD_PAYMENT_TYPE    = 'gift_card';
    const REFUND_GC_PAYMENT_TYPE    = 'refund_gift_card';
    const REWARD_POINT_PAYMENT_TYPE = 'reward_point';
    const PAYPAL_PAYMENT_TYPE       = 'paypal';
    const IZETTLE_PAYMENT_TYPE      = 'izettle';
    const ROUNDING_CASH             = 'rounding_cash';
    const PAYMENT_EXPRESS           = 'payment_express';
    const AUTHORIZE_NET             = 'authorize_net';
    const USAEPAY                   = 'usaepay';
    const MONERIS                   = 'moneris';
    const CARDKNOX                  = 'cardknox';

    protected function _construct() {
        $this->_init('SM\Payment\Model\ResourceModel\RetailPayment');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
