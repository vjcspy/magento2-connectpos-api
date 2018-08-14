<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 05/12/2016
 * Time: 14:49
 */

namespace SM\DiscountPerItem\Helper;


class DiscountPerItemHelper {

    const DISCOUNT_PER_ITEM_KEY         = 'discount_per_item';
    const DISCOUNT_PER_ITEM_PERCENT_KEY = 'retail_discount_per_items_percent';

    public function getItemBaseDiscountCalculationPrice(\Magento\Quote\Model\Quote\Item $item) {
        $discountPrice = $item->getData('base_discount_calculation_price');

        return $discountPrice == null ? $item->getBaseCalculationPrice() : $discountPrice;
    }

    public function getItemDiscount(\Magento\Quote\Model\Quote\Item $item, $currencyRate = 1) {
        if (!$item->getBuyRequest())
            return 0;
        // uu tien discount per item percent truoc
        if (!!$item->getBuyRequest()->getData(self::DISCOUNT_PER_ITEM_PERCENT_KEY)) {
            return $this->getItemBaseDiscountCalculationPrice($item) *
                   floatval($item->getBuyRequest()->getData(self::DISCOUNT_PER_ITEM_PERCENT_KEY)) / 100;
        }
        if (!!$item->getBuyRequest()->getData(self::DISCOUNT_PER_ITEM_KEY)) {
            return floatval($item->getBuyRequest()->getData(self::DISCOUNT_PER_ITEM_KEY)) / $currencyRate;
        }

        return 0;
    }
}