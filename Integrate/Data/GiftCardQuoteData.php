<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 10/13/17
 * Time: 4:48 PM
 */

namespace SM\Integrate\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class GiftCardQuoteData extends ApiDataAbstract {

    public function getBaseGiftcardAmount() {
        return $this->getData('base_giftcard_amount');
    }

    public function getGiftCardCode(){
        return $this->getData('gift_code');
    }

    public function getGiftcardAmount() {
        return $this->getData('giftcard_amount');
    }

    public function getIsValid() {
        return $this->getData('is_valid');
    }
}