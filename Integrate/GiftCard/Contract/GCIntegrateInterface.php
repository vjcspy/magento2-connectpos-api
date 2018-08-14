<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 10/13/17
 * Time: 2:59 PM
 */

namespace SM\Integrate\GiftCard\Contract;


interface GCIntegrateInterface {

    public function saveGCDataBeforeQuoteCollect($giftData);

    /**
     * @return \SM\Integrate\Data\GiftCardQuoteData
     */
    public function getQuoteGCData();

    public function removeGiftCard($giftData);

    public function getRefundToGCProductId();

    public function getGCCodePool();

    public function updateRefundToGCProduct($data);
}