<?php
/**
 * Created by Hung Nguyen - hungnh@smartosc.com
 * Date: 16/08/2018
 * Time: 09:56
 */

namespace SM\Product\Repositories\ProductManagement\ProductOptions;

use Magento\Catalog\Model\ProductFactory;
use SM\Product\Repositories\ProductManagement\ProductOptions;
use SM\Product\Repositories\ProductManagement\ProductPrice;

class M2EEGiftCard extends ProductOptions {

    /**
     * @var \Magento\GiftCard\Block\Catalog\Product\View\Type\Giftcard
     */
    protected $_giftCardViewBlock;
    /**
     * @var \Magento\Store\Model\Store
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \SM\Product\Repositories\ProductManagement\ProductPrice $productPrice,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager  = $storeManager;
        parent::__construct($objectManager, $catalogProduct, $registry, $productFactory, $productPrice);
    }

    /**
     * @return \Magento\GiftCard\Block\Catalog\Product\View\Type\Giftcard
     */
    protected function getGiftCardViewBlock() {
        if (is_null($this->_giftCardViewBlock)) {
            $this->_giftCardViewBlock = $this->getObjectManager()->create('\Magento\GiftCard\Block\Catalog\Product\View\Type\Giftcard');
        }

        return $this->_giftCardViewBlock;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    public function getGiftCardOption(\Magento\Catalog\Model\Product $product) {
        $this->resetProductInBlock($product);

        return [
            'isAllowPreview'       => false,
            'isAllowDesignSelect'  => false,
            'isAllowMessage'       => $this->getGiftCardViewBlock()->isMessageAvailable($product),
            'isAllowHeadline'      => false,
            'isAllowEmail'         => $this->getGiftCardViewBlock()->isEmailAvailable($product),
            'isAllowDeliveryDate'  => false,
            'isAllowOpenAmount'    => $this->getGiftCardViewBlock()->isOpenAmountAvailable($product),
            'isFixedAmount'        => $this->getGiftCardViewBlock()->isAmountAvailable($product),
            'isPhysicalValue'      => $product->getData('giftcard_type') == \Magento\GiftCard\Model\Giftcard::TYPE_PHYSICAL,
            'isCombinedValue'      => $product->getData('giftcard_type') == \Magento\GiftCard\Model\Giftcard::TYPE_COMBINED,
            'isVirtualValue'       => $product->getData('giftcard_type') == \Magento\GiftCard\Model\Giftcard::TYPE_VIRTUAL,
            'isAllowGiftWrapping'  => $product->getGiftWrappingAvailable(),
            'giftWrappingPrice'    => $product->getGiftWrappingPrice(),
            'getAmountOptions'     => $this->getAmounts($product),
            'getAmountOptionValue' => $this->getGiftCardViewBlock()->getAmountSettingsJson($product),
            'getMinCustomAmount'   => $product->getOpenAmountMin(),
            'getMaxCustomAmount'   => $product->getOpenAmountMax(),
            'getTimezones'         => false,
            'getGiftcardTemplates' => false,
        ];
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getAmounts($product)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        $result = [];
        foreach ($product->getGiftcardAmounts() as $amount) {
            if ($amount['website_id'] == '0' || $amount['website_id'] == $websiteId) {
                $result[] = $amount['website_value'];
            }
        }
        sort($result);
        return $result;
    }
}
