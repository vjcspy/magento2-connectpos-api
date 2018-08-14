<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 07/11/2016
 * Time: 16:08
 */

namespace SM\Setting\Repositories\SettingManagement;

use Magento\Tax\Model\Config;

/**
 * Class Tax
 *
 * @package SM\Setting\Repositories\SettingManagement
 */
class Tax extends AbstractSetting implements SettingInterface {

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * Tax constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Tax\Model\Config                          $taxConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->taxConfig = $taxConfig;
        parent::__construct($scopeConfig);
    }

    /**
     * @var string
     */
    protected $CODE = "tax";

    /**
     * @return array
     */
    public function build() {
        // TODO: Implement build() method.
        return [
            'country'                         => $this->getScopeConfig()->getValue(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStore()
            ),
            'region'                          => $this->getScopeConfig()->getValue(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_REGION,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStore()
            ),
            'postcode'                        => $this->getScopeConfig()->getValue(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_POSTCODE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStore()
            ),
            'price_includes_tax'              => $this->getTaxConfig()->priceIncludesTax($this->getStore()),
            'discount_tax'                    => $this->getTaxConfig()->discountTax($this->getStore()),
            'calculation_sequence'            => $this->getTaxConfig()->getCalculationSequence($this->getStore()),
            'shipping_tax_class'              => $this->getTaxConfig()->getShippingTaxClass($this->getStore()),
            'shipping_price_includes_tax'     => $this->getTaxConfig()->shippingPriceIncludesTax($this->getStore()),
            'cross_border_trade_enabled'      => $this->getTaxConfig()->crossBorderTradeEnabled($this->getStore()),
            'based_on'                        => $this->getScopeConfig()->getValue(
                Config::CONFIG_XML_PATH_BASED_ON,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStore()
            ),
            'tax_on_custom_price'             => (int)$this->getScopeConfig()->getValue(
                    Config::CONFIG_XML_PATH_APPLY_ON,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $this->getStore()
                ) == 0,
            'tax_on_original_price'           => (int)$this->getScopeConfig()->getValue(
                    Config::CONFIG_XML_PATH_APPLY_ON,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $this->getStore()
                ) == 1,
            'apply_tax_after_discount'        => $this->getTaxConfig()->applyTaxAfterDiscount($this->getStore()),
            'algorithm'                       => "UNIT_BASE_CALCULATION",
            'display_cart_price_excl_tax'     => $this->getTaxConfig()->displayCartPricesExclTax($this->getStore()),
            'display_cart_subtotal_excl_tax'  => $this->getTaxConfig()->displayCartSubtotalExclTax($this->getStore()),
            'display_sales_subtotal_excl_tax' => $this->getTaxConfig()->displaySalesSubtotalExclTax($this->getStore()),
            'display_cart_shipping_excl_tax'  => $this->getTaxConfig()->displayCartShippingExclTax($this->getStore()),
            'display_sales_shipping_excl_tax' => $this->getTaxConfig()->displaySalesShippingExclTax($this->getStore()),
            'display_sales_prices_excl_tax'   => $this->getTaxConfig()->displaySalesPricesExclTax($this->getStore()),
        ];
    }

    /**
     * @return \Magento\Tax\Model\Config
     */
    public function getTaxConfig() {
        return $this->taxConfig;
    }
}