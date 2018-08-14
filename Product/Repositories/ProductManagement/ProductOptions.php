<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 22/10/2016
 * Time: 15:52
 */

namespace SM\Product\Repositories\ProductManagement;

use Magento\Catalog\Model\ProductFactory;
use SM\Product\Repositories\ProductManagement\ProductPrice;

/**
 * Class ProductOptions
 *
 * @package SM\Product\Repositories\ProductManagement
 */
class ProductOptions {

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $catalogProduct;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \SM\Product\Repositories\ProductManagement\ProductPrice
     */
    private $productPrice;

    /**
     * ProductOptions constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Helper\Product           $catalogProduct
     * @param \Magento\Framework\Registry               $registry
     * @param \Magento\Catalog\Model\ProductFactory     $productFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Framework\Registry $registry,
        ProductFactory $productFactory,
        ProductPrice $productPrice
    ) {
        $this->productFactory = $productFactory;
        $this->objectManager  = $objectManager;
        $this->catalogProduct = $catalogProduct;
        $this->registry       = $registry;
        $this->productPrice   = $productPrice;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     * @throws \Exception
     */
    public function getOptions(\Magento\Catalog\Model\Product $product) {
        $xOptions = [];
        switch ($product->getTypeId()) {
            case 'configurable':
                $xOptions['configurable'] = $this->getOptionsConfigurableProduct($product);
                break;
            case 'bundle':
                $xOptions['bundle'] = $this->getOptionsBundleProduct($product);
                break;
            case 'grouped':
                $xOptions['grouped'] = $this->getAssociatedProducts($product);
                break;
            case 'aw_giftcard':
                /** @var \SM\Product\Repositories\ProductManagement\ProductOptions\AWGiftCard $awGC */
                $awGC                  = $this->objectManager->create('SM\Product\Repositories\ProductManagement\ProductOptions\AWGiftCard');
                $xOptions['gift_card'] = $awGC->getGiftCardOption($product);
                break;
        }

        return $xOptions;
    }

    public function getCustomizableOptions(\Magento\Catalog\Model\Product $product) {
        return $this->getCustomOptionsSimpleProduct($product);
    }

    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager() {
        return $this->objectManager;
    }

    /**
     * @return \Magento\Catalog\Helper\Product
     */
    public function getCatalogProduct() {
        return $this->catalogProduct;
    }

    /**
     * @return \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable
     */
    protected function getConfigurableBlock() {
        return $this->objectManager->create('\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable');
    }

    /**
     * @return \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Bundle
     */
    protected function getBundleBlock() {
        return $this->objectManager->create('\Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Bundle');
    }

    /**
     * @return \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped
     */
    protected function getGroupedBlock() {
        return $this->objectManager->create('\Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    protected function getOptionsConfigurableProduct(\Magento\Catalog\Model\Product $product) {
        $this->resetProductInBlock($product);

        return json_decode($this->getConfigurableBlock()->getJsonConfig(), true);
    }

    /**
     * @param $product
     *
     * @return mixed
     */
    protected function getOptionsBundleProduct(\Magento\Catalog\Model\Product $product) {
        $this->resetProductInBlock($product);
        $this->catalogProduct->setSkipSaleableCheck(true);
        $outputOptions = [];
        $options       = $this->getBundleBlock()->decorateArray($this->getBundleBlock()->getOptions());
        foreach ($options as $option) {
            $selections = [];
            if (is_array($option->getSelections()))
                foreach ($option->getSelections() as $selection) {
                    $selectionData                = $selection->getData();
                    $selectionData['id']          = $selectionData['entity_id'];
                    $selectionData['tier_prices'] = !empty($selectionData['tier_price']) ? $selectionData['tier_price'] : $this->getProductPrice()->getExistingPrices($selection, 'tier_price', true);
                    $selections[]                 = $selectionData;
                }
            $optionData               = $option->getData();
            $optionData['selections'] = $selections;
            $outputOptions[]          = $optionData;
        }

        return [
            'options'    => $outputOptions,
            'type_price' => $product->getPriceType()
        ];
    }

    /**
     * @param $product
     *
     * @return array
     */
    protected function getAssociatedProducts(\Magento\Catalog\Model\Product $product) {
        $outputOptions = [];
        $this->resetProductInBlock($product);
        //$this->getGroupedBlock()->setPreconfiguredValue();
        $_associatedProducts    = $this->getGroupedBlock()->getAssociatedProducts();
        $_hasAssociatedProducts = count($_associatedProducts) > 0;
        if ($_hasAssociatedProducts) {
            foreach ($_associatedProducts as $_item) {
                $_itemData                = $_item->getData();
                $_itemData['tier_prices'] = !empty($_itemData['tier_price']) ? $_itemData['tier_price'] : $this->getProductPrice()->getExistingPrices($_item, 'tier_price', true);
                $outputOptions[]          = $_itemData;
            }
        }

        return $outputOptions;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    protected function getCustomOptionsSimpleProduct(\Magento\Catalog\Model\Product $product) {
        $options = [];
        if ($product->getOptions()) {
            foreach ($product->getOptions() as $value) {
                $custom_option      = $value->getData();
                $values             = $value->getValues();
                $custom_option_data = [];
                if (is_array($values))
                    foreach ($values as $valuess) {
                        $custom_option_data[] = $valuess->getData();

                    }
                $custom_option['data'] = $custom_option_data;
                $options[]             = $custom_option;
            }
        }

        return $options;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry() {
        return $this->registry;
    }


    /**
     * @param $product
     *
     * @return $this
     */
    protected function resetProductInBlock($product) {
        $this->getRegistry()->unregister('current_product');
        $this->getRegistry()->unregister('product');
        $this->getRegistry()->register('current_product', $product);
        $this->getRegistry()->register('product', $product);

        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct() {
        return $this->productFactory->create();
    }

    /**
     * @return \SM\Product\Repositories\ProductManagement\ProductPrice
     */
    public function getProductPrice() {
        return $this->productPrice;
    }

}
