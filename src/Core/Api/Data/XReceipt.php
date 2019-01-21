<?php

namespace SM\Core\Api\Data;


class XReceipt extends \SM\Core\Api\Data\Contract\ApiDataAbstract {

    public function getId() {
        return $this->getData('id');
    }

    public function getLogoImageStatus() {
        return $this->getData('logo_image_status');
    }

    public function getLogoUrl() {
        return $this->getData('logo_url');
    }

    public function getName() {
        return $this->getData('name');
    }

    public function getFooterImageStatus() {
        return $this->getData('footer_image_status');
    }

    public function getFooterUrl() {
        return $this->getData('footer_url');
    }

    public function getHeader() {
        return $this->getData('header');
    }

    public function getFooter() {
        return $this->getData('footer');
    }

    public function getCustomerInfo() {
        return $this->getData('customer_info');
    }

    public function getFontType() {
        return $this->getData('font_type');
    }

    public function getBarcodeSymbology() {
        return $this->getData('barcode_symbology');
    }

    public function getRowTotalInclTax() {
        return $this->getData('row_total_incl_tax');
    }

    public function getSubtotalInclTax() {
        return $this->getData('subtotal_incl_tax');
    }

    public function getEnableBarcode() {
        return $this->getData('enable_barcode');
    }

    public function getEnablePowerText() {
        return $this->getData('enable_power_text');
    }

    public function getOrderInfo() {

        return json_decode($this->getData('order_info'), true);
    }

    public function getCreatedAt() {
        return $this->getData('created_at');
    }

    public function getUpdatedAt() {
        return $this->getData('updated_at');
    }

    public function getIsDefault() {
        return $this->getData('is_default') == 1;
    }

    public function getDayOfWeek() {
        return $this->getData('day_of_week');
    }

    public function getDayOfMonth() {

        return $this->getData('day_of_month');
    }

    public function getMonth() {
        return $this->getData('month');
    }

    public function getYear() {
        return $this->getData('year');
    }

    public function getTime() {
        return $this->getData('time');
    }

    public function getCustomDate() {
        return $this->getData('custom_date');
    }
}
