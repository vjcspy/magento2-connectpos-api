<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 12/01/2017
 * Time: 11:28
 */

namespace SM\Setting\Repositories\SettingManagement;


class Product extends AbstractSetting {

    /**
     * @var string
     */
    protected $CODE = 'product';
    /**
     * @var \SM\CustomSale\Helper\Data
     */
    protected $customSaleHelper;
    /**
     * @var \SM\Product\Repositories\ProductManagement
     */
    private $productManagement;
    /**
     * @var \SM\Product\Helper\ProductHelper
     */
    private $productHelper;

    /**
     * Product constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \SM\CustomSale\Helper\Data                         $customSaleHelper
     * @param \SM\Product\Repositories\ProductManagement         $productManagement
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \SM\CustomSale\Helper\Data $customSaleHelper,
        \SM\Product\Repositories\ProductManagement $productManagement,
        \SM\Product\Helper\ProductHelper $productHelper
    ) {
        $this->customSaleHelper  = $customSaleHelper;
        $this->productHelper    = $productHelper;
        $this->productManagement = $productManagement;
        parent::__construct($scopeConfig);
    }

    /**
     * @return array
     */
    public function build() {
        return [
            'custom_sale_product_id' => $this->customSaleHelper->getCustomSaleId(),
            'product_attributes'     => $this->productHelper->getProductAttributes(), // FIXME: REMOVE IN NEXT VERSION. NOW WE SUPPORT OLD CONNECT POS VERSION.
            'custom_sale_product'    => $this->productManagement->getCustomSaleData($this->getStore(), null)->getOutput(),
        ];
    }
}