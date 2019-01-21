<?php
namespace SM\Shipping\Plugin\Quote;

use Magento\Quote\Model\Quote\Address;
use SM\Shipping\Model\Carrier\RetailStorePickUp;

class SaveOutletPlugin
{
    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $shippingAddress = $addressInformation->getData('shipping_address');
        if ($addressInformation->getData('shipping_method_code') == RetailStorePickUp::METHOD_CODE && $shippingAddress->getExtensionAttributes()->getOutletAddress()) {
            $quote = $this->quoteRepository->getActive($cartId);
            $quote->setOutletId($shippingAddress->getExtensionAttributes()->getOutletAddress());
        }

    }
}