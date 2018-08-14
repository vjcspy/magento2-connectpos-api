<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 01/01/2017
 * Time: 18:06
 */

namespace SM\Sales\Repositories;


use Magento\Framework\DataObject;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

/**
 * Class CreditmemoManagement
 *
 * @package SM\Sales\Repositories
 */
class CreditmemoManagement extends ServiceAbstract {

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;
    /**
     * @var \Magento\Sales\Block\Adminhtml\Items\AbstractItems
     */
    protected $blockItem;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender
     */
    protected $creditmemoSender;
    /**
     * @var \SM\Sales\Repositories\InvoiceManagement
     */
    protected $invoiceManagement;
    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;
    private   $orderHistoryManagement;
    private   $_currentRate;

    /**
     * @var \SM\XRetail\Helper\Data
     */
    private $retailHelper;

    /**
     * @var \SM\Payment\Helper\PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var \SM\Integrate\Helper\Data
     */
    protected $integrateHelperData;


    /**
     * CreditmemoManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface               $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                         $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface            $storeManager
     * @param \SM\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param \Magento\Sales\Block\Adminhtml\Items\AbstractItems    $blockItem
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SM\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        \Magento\Sales\Block\Adminhtml\Items\AbstractItems $blockItem,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $creditmemoSender,
        InvoiceManagement $invoiceManagement,
        \Magento\Tax\Model\Config $taxConfig,
        \SM\Payment\Helper\PaymentHelper $paymentHelper,
        \SM\XRetail\Helper\Data $retailHelper,
        \SM\Integrate\Helper\Data $integrateHelperData,
        \SM\Sales\Repositories\OrderHistoryManagement $orderHistoryManagement
    ) {
        $this->taxConfig              = $taxConfig;
        $this->invoiceManagement      = $invoiceManagement;
        $this->session                = $session;
        $this->creditmemoLoader       = $creditmemoLoader;
        $this->blockItem              = $blockItem;
        $this->objectManager          = $objectManager;
        $this->creditmemoSender       = $creditmemoSender;
        $this->orderHistoryManagement = $orderHistoryManagement;
        $this->paymentHelper            = $paymentHelper;
        $this->retailHelper             = $retailHelper;
        $this->integrateHelperData      = $integrateHelperData;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function creditmemo() {
        if ($this->getRequest()->getParam('is_save') == false) {
            return $this->load();
        }
        else if ($this->getRequest()->getParam('is_save') == true) {
            return $this->save();
        }
        throw new \Exception("Please define action");
    }

    /**
     * @return array
     */
    protected function load() {
        $orderId = $this->getRequest()->getParam('order_id');
        $this->creditmemoLoader->setCreditmemo($this->getCreditmemoData());
        $this->creditmemoLoader->setOrderId($orderId);
        $creditmemo = $this->creditmemoLoader->load();

        $order = $creditmemo->getOrder();
        if ($order->getPayment()->getMethod() != \SM\Payment\Model\RetailMultiple::PAYMENT_METHOD_RETAILMULTIPLE_CODE) {
            throw new \Exception("Cannot create online refund for X-Retail");
        }

        return $this->getOutputCreditmemo($creditmemo);
    }

    protected function getCreditmemoData() {
        $data = $this->getRequest()->getParam('creditmemo');

        if (isset($data['shipping_amount']) && !is_nan($data['shipping_amount'])) {
            $data['shipping_amount'] = $data['shipping_amount'] / $this->getCurrentRate();
        }

        return $data;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function save() {
        $orderId = $this->getRequest()->getParam('order_id');
        $storeId = $this->getRequest()->getParam('store_id');
        $data    = $this->getCreditmemoData();
        $this->creditmemoLoader->setOrderId($orderId);
        //$this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
        $this->creditmemoLoader->setCreditmemo($this->getCreditmemoData());
        //$this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
        $creditmemo = $this->creditmemoLoader->load();

        // check order create by X-Retail
        $order = $creditmemo->getOrder();
        if ($order->getPayment()->getMethod() != \SM\Payment\Model\RetailMultiple::PAYMENT_METHOD_RETAILMULTIPLE_CODE) {
            throw new \Exception("Cannot create online refund for X-Retail");
        }

        if ($creditmemo) {
            if (!$creditmemo->isValidGrandTotal()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The credit memo\'s total must be positive.')
                );
            }

            if (!empty($data['comment_text'])) {
                $creditmemo->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );

                $creditmemo->setCustomerNote($data['comment_text']);
                $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
            }

            if (isset($data['do_offline'])) {
                //do not allow online refund for Refund to Store Credit
                if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Cannot create online refund for Refund to Store Credit.')
                    );
                }
            }

            // check payment data
            if (is_array($data['payment_data']) && count($data['payment_data']) <= 2) {
                // when exchange can have two payment method.
            }
            else {
                throw new \Exception("Refund only accept one payment method");
            }

            $creditmemoManagement = $this->objectManager->create(
                'Magento\Sales\Api\CreditmemoManagementInterface'
            );
            $creditmemoManagement->refund($creditmemo, (bool)$data['do_offline']);

            if (!empty($data['send_email'])) {
                $this->creditmemoSender->send($creditmemo);
            }

            // for case refund using only giftcard
            if ($data['payment_data'] == null && $this->integrateHelperData->isAHWGiftCardxist() && $this->integrateHelperData->isIntegrateGC() ) {
                $created_at       = $this->retailHelper->getCurrentTime();
                $giftCardPaymentId       = $this->paymentHelper->getPaymentIdByType(\SM\Payment\Model\RetailPayment::GIFT_CARD_PAYMENT_TYPE);
                $data['payment_data'][0] = [
                    "id"                    => $giftCardPaymentId,
                    "type"                  => \SM\Payment\Model\RetailPayment::GIFT_CARD_PAYMENT_TYPE,
                    "title"                 => "Gift Card",
                    "refund_amount"         => $creditmemo->getGrandTotal(),
                    "data"                  => [],
                    "isChanging"            => false,
                    "allow_amount_tendered" => true,
                    "is_purchase"           => 0,
                    "created_at"            => $created_at,
                    "payment_data"          => []
                ];
            }
                // fix refund amount
                $data['payment_data'][0]['amount'] = -$creditmemo->getGrandTotal();

            return $this->invoiceManagement->addPayment(
                [
                    'payment_data' => $data['payment_data'],
                    'order_id'     => $this->getRequest()->getParam('order_id'),
                    "outlet_id"    => $this->getRequest()->getParam('outlet_id'),
                    "register_id"  => $this->getRequest()->getParam('register_id'),
                    'store_id'     => $storeId
                ],
                true);
        }
        else {
            throw new \Exception("Can't find creditmemo data");
        }
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     *
     * @return array
     */
    public function getOutputCreditmemo(\Magento\Sales\Model\Order\Creditmemo $creditmemo) {
        $data             = [
            'order_id'  => $creditmemo->getOrderId(),
            'retail_id' => $creditmemo->getOrder()->getData('retail_id')
        ];
        $data['customer'] = [
            'customer_id' => $creditmemo->getOrder()->getCustomerId()
        ];

        $data['items'] = [];
        $this->blockItem->setOrder($creditmemo->getOrder());
        foreach ($creditmemo->getAllItems() as $item) {
            /** @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
            $_item               = [];
            $_item['item_id']    = $item->getOrderItemId();
            $_item['product_id'] = $item->getProductId();
            $_item['name']       = $item->getName();
            $_item['sku']        = $item->getSku();
            $_item['parent_id']  = $item->getOrderItem()->getParentItemId();
            $_item['type_id']    = $item->getOrderItem()->getProductType();
            if ($item->getOrderItem()->isChildrenCalculated())
                $_item['children_calculated'] = $item->getOrderItem()->isChildrenCalculated();
            $_item['price']              = $item->getPrice();
            $_item['price_incl_tax']     = $item->getPriceInclTax();
            $_item['qty_ordered']        = $item->getOrderItem()->getQtyOrdered() * 1;
            $_item['qty_invoiced']       = $item->getOrderItem()->getQtyInvoiced() * 1;
            $_item['qty_shipped']        = $item->getOrderItem()->getQtyShipped() * 1;
            $_item['qty_refunded']       = $item->getOrderItem()->getQtyRefunded() * 1;
            $_item['qty_canceled']       = $item->getOrderItem()->getQtyCanceled() * 1;
            $_item['can_back_to_stock']  = ($this->blockItem->canParentReturnToStock($item)
                                            && $this->blockItem->canReturnItemToStock($item)) ? true : false;
            $_item['back_to_stock']      = $_item['can_back_to_stock'] ? true : false;
            $_item['can_edit_qty']       = $this->blockItem->canEditQty();
            $_item['qty_to_refund']      = $item->getOrderItem()->getQtyToRefund();
            $_item['qty']                = $item->getQty();
            $_item['row_total']          = $item->getRowTotal();
            $_item['row_total_incl_tax'] = $item->getRowTotalInclTax();
            $_item['tax_amount']         = $item->getTaxAmount();
            $_item['discount_amount']    = $item->getDiscountAmount();
            $_item['is_qty_decimal']     = $item->getOrderItem()->getIsQtyDecimal();
            $productOption               = $item->getOrderItem()->getProductOptions();
            if (isset($productOption['info_buyRequest']['custom_sale'])) {
                $_item['custom_sale_name'] = $productOption['info_buyRequest']['custom_sale']['name'];
            }
            $data['items'][] = $_item;
        }

        $totals                               = [];
        $totals['subtotal']                   = $creditmemo->getSubtotal();
        $totals['subtotal_incl_tax']          = $creditmemo->getSubtotalInclTax();
        $totals['shipping']                   = $creditmemo->getShippingAmount();
        $totals['shipping_incl_tax']          = $creditmemo->getShippingInclTax();
        $totals['discount_amount']            = $creditmemo->getDiscountAmount();
        $totals['tax_amount']                 = $creditmemo->getTaxAmount();
        $totals['grand_total']                = $creditmemo->getGrandTotal();
        $data['totals']                       = $totals;
        $data['adjustment']                   = $creditmemo->getAdjustmentPositive() - $creditmemo->getAdjustmentNegative();
        $data['is_display_shipping_incl_tax'] = $this->taxConfig->displaySalesShippingInclTax($creditmemo->getOrder()->getStoreId());

        if ($data['is_display_shipping_incl_tax']) {
            $shipping = $creditmemo->getShippingInclTax();
        }
        else {
            $shipping = $creditmemo->getShippingAmount();
        }
        $data['shipping_amount']     = $shipping;
        $data['retail_has_shipment'] = $creditmemo->getOrder()->getData('retail_has_shipment');
        $data['total_paid']          = $creditmemo->getOrder()->getData('total_paid');
        $data['total_refunded']      = $creditmemo->getOrder()->getData('total_refunded');

        return $data;
    }

    private function getCurrentRate() {
        if ($this->_currentRate === null) {
            $orderId = $this->getRequest()->getParam('order_id');
            $order = $this->objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            $this->_currentRate = $order->getStore()
                                        ->getBaseCurrency()
                                        ->convert(1, $order->getOrderCurrencyCode());
        }

        return $this->_currentRate;
    }
}