<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 06/12/2016
 * Time: 14:20
 */

namespace SM\DiscountPerItem\Block\Adminhtml\Order\Create\Totals;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class DiscountPerItem extends \Magento\Sales\Block\Adminhtml\Order\Create\Totals\DefaultTotals {

    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/create/totals/discountperitem.phtml';

    /**
     * Tax config
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote    $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create  $orderCreate
     * @param \Magento\Sales\Helper\Data              $salesData
     * @param \Magento\Sales\Model\Config             $salesConfig
     * @param PriceCurrencyInterface                  $priceCurrency
     * @param \Magento\Tax\Model\Config               $taxConfig
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->_taxConfig = $taxConfig;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $salesData, $salesConfig, $data);
    }

    public function displayDiscountPerItem() {
        $discount = $this->getQuote()->getShippingAddress()->getData('retail_discount_per_item_amount');

        return $discount ? $discount : false;
    }
}
