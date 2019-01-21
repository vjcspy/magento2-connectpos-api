<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 03/12/2016
 * Time: 22:39
 */

namespace SM\Sales\Repositories;

use Magento\Framework\DataObject;
use SM\Integrate\Model\WarehouseIntegrateManagement;
use SM\Payment\Model\RetailMultiple;
use SM\Shipping\Model\Carrier\RetailShipping;
use SM\XRetail\Repositories\Contract\ServiceAbstract;
use Magento\Sales\Model\Order;

/**
 * Class OrderManagement
 *
 * @package SM\Sales\Repositories
 */
class OrderManagement extends ServiceAbstract {

    static $IS_COLLECT_RULE       = true;
    static $ALLOW_BACK_ORDER      = true;
    static $FROM_API              = false;
    static $ORDER_HAS_CUSTOM_SALE = false;
    static $SAVE_ORDER            = false;

    const USING_REFUND_TO_GIFT_CARD = 'using_refund_to_GC';

    static $MESSAGE_ERROR = [];

    const DISCOUNT_WHOLE_ORDER_KEY = 'discount_whole_order';

    const RETAIL_ORDER_PARTIALLY_PAID_AWAIT_COLLECTION    = 16;
    const RETAIL_ORDER_PARTIALLY_PAID_PICKING_IN_PROGRESS = 15;
    const RETAIL_ORDER_PARTIALLY_PAID_AWAIT_PICKING       = 14;
    const RETAIL_ORDER_PARTIALLY_PAID_SHIPPED             = 13;
    const RETAIL_ORDER_PARTIALLY_PAID_NOT_SHIPPED         = 12;
    const RETAIL_ORDER_PARTIALLY_PAID                     = 11;

    const RETAIL_ORDER_COMPLETE_AWAIT_COLLECTION    = 26;
    const RETAIL_ORDER_COMPLETE_PICKING_IN_PROGRESS = 25;
    const RETAIL_ORDER_COMPLETE_AWAIT_PICKING       = 24;
    const RETAIL_ORDER_COMPLETE_SHIPPED             = 23;
    const RETAIL_ORDER_COMPLETE_NOT_SHIPPED         = 22;
    const RETAIL_ORDER_COMPLETE                     = 21;

    const RETAIL_ORDER_PARTIALLY_REFUND_AWAIT_COLLECTION    = 36;
    const RETAIL_ORDER_PARTIALLY_REFUND_PICKING_IN_PROGRESS = 35;
    const RETAIL_ORDER_PARTIALLY_REFUND_AWAIT_PICKING       = 34;
    const RETAIL_ORDER_PARTIALLY_REFUND_SHIPPED             = 33;
    const RETAIL_ORDER_PARTIALLY_REFUND_NOT_SHIPPED         = 32;
    const RETAIL_ORDER_PARTIALLY_REFUND                     = 31;

    const RETAIL_ORDER_FULLY_REFUND = 40;

    const RETAIL_ORDER_EXCHANGE_AWAIT_COLLECTION    = 56;
    const RETAIL_ORDER_EXCHANGE_PICKING_IN_PROGRESS = 55;
    const RETAIL_ORDER_EXCHANGE_AWAIT_PICKING       = 54;
    const RETAIL_ORDER_EXCHANGE_SHIPPED             = 53;
    const RETAIL_ORDER_EXCHANGE_NOT_SHIPPED         = 52;
    const RETAIL_ORDER_EXCHANGE                     = 51;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var \SM\XRetail\Model\UserOrderCounterFactory
     */
    protected $userOrderCounterFactory;
    /**
     * @var \SM\Sales\Repositories\ShipmentDataManagement
     */
    protected $shipmentDataManagement;
    /**
     * @var \SM\Sales\Repositories\InvoiceManagement
     */
    protected $invoiceManagement;
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProduct;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \SM\Payment\Model\RetailPaymentFactory
     */
    protected $retailPaymentFactory;
    /**
     * @var \SM\Shift\Model\RetailTransactionFactory
     */
    protected $retailTransactionFactory;
    /**
     * @var \SM\Integrate\Helper\Data
     */
    protected $integrateHelperData;
    /**
     * @var \SM\Integrate\Model\RPIntegrateManagement
     */
    protected $rpIntegrateManagement;
    /**
     * @var \SM\Shift\Helper\Data
     */
    private $shiftHelper;
    /**
     * @var \SM\Sales\Model\OrderSyncErrorFactory
     */
    private $orderSyncErrorFactory;
    /**
     * @var \SM\Sales\Repositories\OrderHistoryManagement
     */
    private $orderHistoryManagement;

    /**
     * @var \SM\XRetail\Helper\Data
     */
    private $retailHelper;

    private $_currentRate;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    private $_taxHelper;
    /**
     * @var \SM\Integrate\Model\GCIntegrateManagement
     */
    private $gcIntegrateManagement;

    private $_requestOrderData;

    private $_isRefundToGC;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \SM\Payment\Helper\PaymentHelper
     */
    private $paymentHelper;

    /**
     * OrderManagement constructor.
     *
     * @param \SM\XRetail\Helper\DataConfig                              $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface                 $storeManager
     * @param \Magento\Backend\App\Action\Context                        $context
     * @param \Magento\Framework\Registry                                $registry
     * @param \SM\XRetail\Model\UserOrderCounterFactory                  $userOrderCounterFactory
     * @param \SM\Sales\Repositories\ShipmentManagement                  $shipmentManagement
     * @param \SM\Sales\Repositories\InvoiceManagement                   $invoiceManagement
     * @param \Magento\Catalog\Helper\Product                            $cataglogProduct
     * @param \Magento\Customer\Model\Session                            $customerSession
     * @param \SM\Payment\Model\RetailPaymentFactory                     $retailPaymentFactory
     * @param \SM\Shift\Model\RetailTransactionFactory                   $retailTransactionFactory
     * @param \SM\Shift\Helper\Data                                      $shiftHelper
     * @param \SM\Integrate\Helper\Data                                  $integrateHelperData
     * @param \SM\Integrate\Model\RPIntegrateManagement                  $RPIntegrateManagement
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     */
    public function __construct(
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \SM\XRetail\Helper\Data $retailHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \SM\XRetail\Model\UserOrderCounterFactory $userOrderCounterFactory,
        ShipmentManagement $shipmentManagement,
        InvoiceManagement $invoiceManagement,
        \Magento\Catalog\Helper\Product $cataglogProduct,
        \Magento\Customer\Model\Session $customerSession,
        \SM\Payment\Model\RetailPaymentFactory $retailPaymentFactory,
        \SM\Payment\Helper\PaymentHelper $paymentHelper,
        \SM\Shift\Model\RetailTransactionFactory $retailTransactionFactory,
        \SM\Shift\Helper\Data $shiftHelper,
        \SM\Integrate\Helper\Data $integrateHelperData,
        \SM\Integrate\Model\RPIntegrateManagement $RPIntegrateManagement,
        \SM\Integrate\Model\GCIntegrateManagement $GCIntegrateManagement,
        \SM\Sales\Model\OrderSyncErrorFactory $orderSyncErrorFactory,
        OrderHistoryManagement $orderHistoryManagement,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool

    ) {
        $this->retailTransactionFactory = $retailTransactionFactory;
        $this->retailPaymentFactory     = $retailPaymentFactory;
        $this->customerSession          = $customerSession;
        $this->catalogProduct           = $cataglogProduct;
        $this->shipmentDataManagement   = $shipmentManagement;
        $this->invoiceManagement        = $invoiceManagement;
        $this->context                  = $context;
        $this->registry                 = $registry;
        $this->userOrderCounterFactory  = $userOrderCounterFactory;
        $this->shiftHelper              = $shiftHelper;
        $this->integrateHelperData      = $integrateHelperData;
        $this->rpIntegrateManagement    = $RPIntegrateManagement;
        $this->gcIntegrateManagement    = $GCIntegrateManagement;
        $this->orderSyncErrorFactory    = $orderSyncErrorFactory;
        $this->orderHistoryManagement   = $orderHistoryManagement;
        $this->retailHelper             = $retailHelper;
        $this->_taxHelper               = $taxHelper;
        $this->orderCollectionFactory   = $collectionFactory;
        $this->orderFactory             = $orderFactory;

        $this->resourceConnection = $resourceConnection;
        $this->metadataPool       = $metadataPool;
        $this->paymentHelper      = $paymentHelper;
        parent::__construct($context->getRequest(), $dataConfig, $storeManager);
    }

    /**
     * @throws \Exception
     */
    public function loadOrderData($isSaveOrder = false) {
        // see XRT-388: not collect all selection of bundle product because it not salable
        $this->catalogProduct->setSkipSaleableCheck(true);

        $this->transformData()
             ->checkShift()
             ->checkCustomerGroup()
             ->checkOutlet()
             ->checkRegister()
             ->checkRetailAdditionData()
             ->checkOfflineMode()
             ->checkIntegrateWh();

        if ($isSaveOrder === true) {
            $this->checkOrderCount()
                 ->checkXRefNumCardKnox();
        }

        try {
            $this->_initSession()
                // We must get quote after session has been created
                 ->checkShippingMethod()
                 ->checkDiscountWholeOrder()
                 ->_processActionData($isSaveOrder ? "check" : null);
        }
        catch (\Exception $e) {
            $this->clear();
            throw new \Exception($e->getMessage());
        }

        $data = null;
        if (!$isSaveOrder) {
            $data = $this->getOutputLoadData();
            $this->clear();
        }

        return $data;
    }

    /**
     * @throws \Exception
     */
    public function updateOrderNote() {
        $data = $this->getRequest()->getParams()['noteData'];

        /** @var  \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
        $collection = $this->orderCollectionFactory->create();

        $collection->addFieldToFilter('entity_id', $data['order_id']);
        $dataOrder = $collection->getFirstItem();

        if ($dataOrder->getId()) {
            $dataOrder->setData('retail_note', $data['retail_note']);
            $this->saveNoteToOrderAlso($dataOrder, $data['retail_note']);
            $dataOrder->save();

            $criteria = new DataObject(['entity_id' => $dataOrder->getEntityId(), 'storeId' => $dataOrder->getStoreId()]);

            return $this->orderHistoryManagement->loadOrders($criteria);
        }
    }


    /**
     * @throws \Exception
     */
    public function saveOrder() {
        self::$SAVE_ORDER = true;
        $this->loadOrderData(true);
        try {
            $order = $this->_getOrderCreateModel()
                          ->setIsValidate(true)
                          ->createOrder();
            $this->savePaymentTransaction($order);
            $this->saveNoteToOrderAlso($order);
        }
        catch (\Exception $e) {
            if (isset($order) && !!$order->getId()) {
                $order->setData('retail_note', $order->getData('retail_note') . ' - ' . $e->getMessage());
                $order->save();
            }
            elseif ($this->getRequest()->getParam('orderOffline')) {
                $this->saveOrderError($this->getRequest()->getParam('orderOffline'), $e);
            }

            throw new \Exception($e->getMessage());
        }
        finally {
            $this->clear();
            if (isset($order) && !!$order->getId()) {
                if (!$this->getRequest()->getParam('retail_has_shipment') && !$this->_getQuote()->isVirtual()) {
                    try {
                        $this->shipmentDataManagement->ship($order->getId());
                    }
                    catch (\Exception $e) {
// ship error
                        if ($e->getMessage() === 'Negative quantity is not allowed, stock movement can not be created'
                            || $e->getMessage()
                               === 'Negative quantity is not allowed') {
                            self::$MESSAGE_ERROR[] = 'can_not_create_shipment_with_negative_qty';
                        }
                    }
                }

                try {
                    $this->invoiceManagement->checkPayment($order);
                }
                catch (\Exception $e) {
// invoice error
                }

                $this->saveOrderTaxInTableShift($order);
            }
        }

        if ($this->_isRefundToGC && !!$this->getRequest()->getParam('order_refund_id')) {
            /** @var \Magento\Sales\Model\Order $order */
            $refundOrder = $this->orderFactory->create();
            $refundOrder->load($this->getRequest()->getParam('order_refund_id'));
            if ($refundOrder->getId()) {
                $splitData = json_decode($refundOrder->getPayment()->getAdditionalInformation('split_data'), true);
                if($splitData) {
                    foreach ($splitData as &$paymentData) {
                        if (is_array($paymentData) && $paymentData['type'] == 'refund_gift_card' && $paymentData['is_purchase'] == 0) {
                            $gcProduct = $order->getItemsCollection()->getFirstItem();
                            if ($this->integrateHelperData->isAHWGiftCardxist() && $this->integrateHelperData->isIntegrateGC()) {
                                $paymentData['gc_created_codes'] = $gcProduct->getData('product_options')['aw_gc_created_codes'][0];
                                $paymentData['gc_amount']        = $gcProduct->getData('product_options')['aw_gc_amount'];
                            }
                            else if ($this->integrateHelperData->isGiftCardMagento2EE() && $this->integrateHelperData->isIntegrateGC()) {
                                $paymentData['gc_created_codes'] = $gcProduct->getData('product_options')['giftcard_created_codes'][0];
                                $paymentData['gc_amount']        = $paymentData['amount'];
                            }
                        }
                    }
                    $refundOrder->getPayment()->setAdditionalInformation('split_data', json_encode($splitData))->save();
                }
            }
            $criteria = new DataObject(

                [
                    'entity_id' => $order->getEntityId() . "," . $refundOrder->getEntityId(),
                    'storeId'   => $this->_requestOrderData['store_id'],
                    'outletId'  => $this->_requestOrderData['outlet_id']]);

            return $this->orderHistoryManagement->loadOrders($criteria);
        }

        $criteria = new DataObject(
            [
                'entity_id' => $order->getEntityId(),
                'storeId'   => $this->_requestOrderData['store_id'],
                'outletId'  => $this->_requestOrderData['outlet_id']]);

        return $this->orderHistoryManagement->loadOrders($criteria);
    }

    /**
     * @param $orderOffline
     * @param $e
     *
     * @return $this
     * @throws \Exception
     */
    protected function saveOrderError($orderOffline, $e) {
        /** @var \SM\Sales\Model\OrderSyncError $orderError */
        $orderError                  = $this->orderSyncErrorFactory->create();
        $orderOffline['pushed']      = 3; // mark as error
        $orderOffline['retail_note'] = $e->getMessage();
        $orderError->setData('order_offline', json_encode($orderOffline))
                   ->setData(
                       'retail_id',
                       $this->getRequest()->getParam('retail_id'))
                   ->setData('store_id', $this->getRequest()->getParam('store_id'))
                   ->setData('outlet_id', $this->getRequest()->getParam('outlet_id'))
                   ->setData('message', $e->getMessage());
        $orderError->save();

        return $this;
    }

    /**
     * To fix amount of exchange order
     *
     * @return $this
     * @throws \Exception
     */
    protected function checkExchange($isSave) {
        if (!$isSave)
            return $this;
        $data  = $this->getRequest()->getParams();
        $order = $data['order'];
        if (isset($order['is_exchange']) && $order['is_exchange'] == true) {
            $this->registry->unregister('is_exchange');
            $this->registry->register('is_exchange', true);
            if ($order['payment_method'] !== RetailMultiple::PAYMENT_METHOD_RETAILMULTIPLE_CODE
                || !is_array($order['payment_data'])
                || count($order['payment_data']) > 2
            )
                throw new \Exception("Order payment data for exchange not valid");

            if ($order['payment_data'] == null && $this->isIntegrateGC()) {
                $created_at               = $this->retailHelper->getCurrentTime();
                $giftCardPaymentId        = $this->paymentHelper->getPaymentIdByType(\SM\Payment\Model\RetailPayment::GIFT_CARD_PAYMENT_TYPE);
                $order['payment_data'][0] = [
                    "id"                    => $giftCardPaymentId,
                    "type"                  => \SM\Payment\Model\RetailPayment::GIFT_CARD_PAYMENT_TYPE,
                    "title"                 => "Gift Card",
                    "refund_amount"         => $this->_getQuote()->getGrandTotal(),
                    "data"                  => [],
                    "isChanging"            => true,
                    "allow_amount_tendered" => true,
                    "is_purchase"           => 1,
                    "created_at"            => $created_at,
                    "payment_data"          => []
                ];
            }
            $order['payment_data'][0]['amount']      = $this->_getQuote()->getGrandTotal();
            $order['payment_data'][0]['is_purchase'] = 1;
            $order['payment_data']['store_id']       = $this->getRequest()->getParam('store_id');
            $data['order']                           = $order;
        }
        if (isset($order['payment_data'])) {
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($order['payment_data']);
            $this->_getOrderCreateModel()->setPaymentData($order['payment_data']);
        }
        $this->getRequest()->setParams($data);

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function checkShift() {
        $data         = $this->getRequest()->getParams();
        $openingShift = $this->shiftHelper->getShiftOpening($data['outlet_id'], $data['register_id']);
        if (!$openingShift->getData('id')) {
            throw new \Exception("No Shift are opening");
        }
        $this->registry->register('opening_shift', $openingShift);

        return $this;
    }

    /**
     * @param $orderData
     *
     * @throws \Exception
     */
    protected function savePaymentTransaction($orderData) {
        $data  = $this->getRequest()->getParams();
        $order = $data['order'];
        if (isset($order['payment_method']) && $order['payment_method'] == RetailMultiple::PAYMENT_METHOD_RETAILMULTIPLE_CODE) {
            $openingShift = $this->registry->registry('opening_shift');
            if (isset($order['payment_data']) && is_array($order['payment_data'])) {
                foreach ($order['payment_data'] as $payment_datum) {
                    if (!is_array($payment_datum)) {
                        continue;
                    }
                    if (!isset($payment_datum['id']) || !$payment_datum['id']) {
                        throw new \Exception("Payment data not valid");
                    }
                    $created_at = $this->retailHelper->getCurrentTime();
                    $_p         = $this->retailTransactionFactory->create();
                    $_p->addData(
                        [
                            'outlet_id'     => $data['outlet_id'],
                            'register_id'   => $data['register_id'],
                            'shift_id'      => $openingShift->getData('id'),
                            'payment_id'    => $payment_datum['id'],
                            'payment_title' => $payment_datum['title'],
                            'payment_type'  => $payment_datum['type'],
                            'amount'        => $payment_datum['amount'],
                            'is_purchase'   => 1,
                            "created_at"    => $created_at,
                            'order_id'      => $orderData->getData('entity_id')]
                    )->save();
                }
            }
        }
    }


    /**
     * save total tax into shift table when create order
     */
    protected function saveOrderTaxInTableShift($orderData) {
        $orderID        = $orderData->getId();
        $state          = $this->orderFactory->create()->load($orderID)->getData()['state'];
        $taxClassAmount = [];
        if ($orderData instanceof Order) {
            $taxClassAmount = $this->_taxHelper->getCalculatedTaxes($orderData);
        }
        $data            = $this->getRequest()->getParams();
        $tax_amount      = $orderData->getData('tax_amount');
        $base_tax_amount = $orderData->getData('base_tax_amount');
        //if (isset($tax_amount) && $tax_amount > 0) {
        $openingShift = $this->shiftHelper->getShiftOpening($data['outlet_id'], $data['register_id']);
        if (!$openingShift) {
            throw new Exception("No shift are opening");
        }


        $currentTax     = floatval($openingShift->getData('total_order_tax')) + floatval($tax_amount);
        $currentBaseTax = floatval($openingShift->getData('base_total_order_tax')) + floatval($base_tax_amount);
        $currentTaxData = json_decode($openingShift->getData('detail_tax'), true);

        $currentPoint_spent  = floatval($openingShift->getData('point_spent'));
        $currentPoint_earned = floatval($openingShift->getData('point_earned'));
        if ($this->integrateHelperData->isAHWRewardPoints() && $this->integrateHelperData->isIntegrateRP() && $state === 'complete') {
            $connection            = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata('Aheadworks\RewardPoints\Api\Data\TransactionInterface')->getEntityConnectionName()
            );
            $select_transaction_id = $connection->select()
                                                ->from($this->resourceConnection->getTableName('aw_rp_transaction_entity'))
                                                ->where('entity_id =' . $orderData->getId());

            $transaction_id = $connection->fetchOne($select_transaction_id, ['transaction_id']);

            $balance = $this->rpIntegrateManagement->getTransactionByOrder($transaction_id);
            if ($balance > 0) {
                $currentPoint_earned += floatval($balance ? $balance : 0);
            }
            else {
                $currentPoint_spent += floatval($balance ? $balance : 0);
            }

        }

        if (count($taxClassAmount) > 0) {
            foreach ($taxClassAmount as $taxDetail) {
                $title = $taxDetail['title'] . '(' . $taxDetail['percent'] . ' %)';
                if (isset($currentTaxData[$title])) {
                    $currentTaxData[$title] = $currentTaxData[$title] + $taxDetail['tax_amount'];
                }
                else {
                    $currentTaxData[$title] = $taxDetail['tax_amount'];
                }
            }
        }
        $openingShift->setData('total_order_tax', "$currentTax")
                     ->setData('base_total_order_tax', "$currentBaseTax")
                     ->setData('detail_tax', json_encode($currentTaxData))
                     ->setData('point_earned', $currentPoint_earned)
                     ->setData('point_spent', $currentPoint_spent)
                     ->save();

    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    protected function createInvoice(\Magento\Sales\Model\Order $order) {

    }

    /**
     *
     */
    public function clear() {
        $this->_getSession()->clearStorage()->destroy();
    }

    /**
     * Process request data with additional logic for saving quote and creating order
     *
     * @param string $action
     *
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _processActionData($action = null) {
        if ($this->getRetailConfig()->isIntegrate()) {
            $eventData = [
                'order_create_model' => $this->_getOrderCreateModel(),
                'request_model'      => $this->getRequest(),
                'session'            => $this->_getSession(),
            ];
            $this->getContext()->getEventManager()->dispatch('adminhtml_sales_order_create_process_data_before', $eventData);
        }

        /*
         * Must remove all address because when magento get quote will collect total, at that time quote hasn't address => magento will use default address
         * After, when we use function setBillingAddress magento still check $customerAddressId -> it already existed -> can't save new billing address
         */
        $this->_getOrderCreateModel()->getQuote()->removeAllAddresses();

        $data = $this->getRequest()->getParam('order');
        /**
         * Saving order data
         */
        if ($data) {
            $this->_getOrderCreateModel()->importPostData($data);
        }

        /**
         * Initialize catalog rule data
         */
        if (self::$IS_COLLECT_RULE) {
            $this->_getOrderCreateModel()->initRuleData();
        }

        /**
         * init first billing address, need for virtual products
         */
        $this->_getOrderCreateModel()->getBillingAddress();

        /**
         * Flag for using billing address for shipping
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual()) {
            $syncFlag       = $this->getRequest()->getPost('shipping_as_billing');
            $shippingMethod = $this->_getOrderCreateModel()->getShippingAddress()->getShippingMethod();
            if ($syncFlag === null
                && $this->_getOrderCreateModel()->getShippingAddress()->getSameAsBilling()
                && empty($shippingMethod)
            ) {
                $this->_getOrderCreateModel()->setShippingAsBilling(1);
            }
            else {
                $this->_getOrderCreateModel()->setShippingAsBilling((int)$syncFlag);
            }
        }

        /**
         * Change shipping address flag
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual() && $this->getRequest()->getPost('reset_shipping')
        ) {
            $this->_getOrderCreateModel()->resetShippingMethod(true);
        }

        /**
         * Adding products to quote from special grid
         */
        if ($this->getRequest()->has('items') && !$this->getRequest()->getPost('update_items') && !($action == 'save')
        ) {
            $items = $this->getRequest()->getParam('items');
            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->addProducts($items);
        }

        if ($this->getRetailConfig()->isIntegrate()) {
            $eventData = [
                'order_create_model' => $this->_getOrderCreateModel(),
                'request'            => $this->getRequest()->getPostValue(),
            ];

            $this->getContext()->getEventManager()->dispatch('adminhtml_sales_order_create_process_data', $eventData);
        }

        $this->checkIntegrateRP()
             ->checkIntegrateGC();

        // Collect shipping rate
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual()) {
            $this->_getOrderCreateModel()
                 ->getShippingAddress()
                 ->setLimitCarrier(['retailshipping'])
                 ->setCollectShippingRates(true);

            /*
             * Retail luôn sử dụng multiple payment
             */
            //if ($paymentData = $this->getRequest()->getPost('payment')) {
            //    $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            //}

            $this->_getOrderCreateModel()
                 ->getQuote()
                 ->setTotalsCollectedFlag(false);
        }

        /*
        *  Need unset data: cached_items_all. Because it's cache when collect total at the first time when haven't any item in quote.
        *  After, we collect it will show error not shipping has set because this can't collect shipping rates(no items)
        */
        $this->_getQuote()->getBillingAddress()->unsetData("cached_items_all");
        $this->_getQuote()->getShippingAddress()->unsetData("cached_items_all");

        if (isset($data['payment_data']) && $data['payment_method'] == RetailMultiple::PAYMENT_METHOD_RETAILMULTIPLE_CODE) {
            $this->_getOrderCreateModel()
                 ->getQuote()
                 ->setTotalsCollectedFlag(false);
            $data['payment_data']['store_id'] = $this->getRequest()->getParam('store_id');
            /**
             * There may be an error in here  Magento\Quote\Model\Quote\Payment
             * $method = parent::getMethodInstance();
             * $method->setStore($this->getQuote()->getStoreId());
             * Can't get StoreId because quote is null.
             * Magento can't set quote to payment in Magento\Quote\Model\Quote:getPayment() - will set current quote to payment here.
             * But we can't get quote from session quote because it check quoteId()(if magento check id !== null instead will not occur error)
             * We don't have to fix this. Only need restrict user assign admin store to outlet.
             **/
            //$this->_getOrderCreateModel()->getQuote()->getPayment()->addData($data['payment_data']);
            $this->_getOrderCreateModel()->setPaymentData($data['payment_data']);
        }
        else if (!$this->_getOrderCreateModel()->getQuote()->isVirtual()) {
            $this->_getOrderCreateModel()->collectShippingRates();
        }
        else {
            $this->_getOrderCreateModel()
                 ->getQuote()
                 ->setTotalsCollectedFlag(false)
                 ->collectTotals();
        }

        $this->checkExchange($action == 'check');

        $this->_getOrderCreateModel()->saveQuote();

        $data       = $this->getRequest()->getParam('order');
        $couponCode = '';
        if (isset($data) && isset($data['coupon']['code'])) {
            $couponCode = trim($data['coupon']['code']);
        }

        if (!empty($couponCode)) {
            $isApplyDiscount = false;
            foreach ($this->_getQuote()->getAllItems() as $item) {
                if (!$item->getNoDiscount()) {
                    $isApplyDiscount = true;
                    break;
                }
            }
            if (!$isApplyDiscount) {
                throw new \Exception(
                    __(
                        '"%1" coupon code was not applied. Do not apply discount is selected for item(s)',
                        $this->escaper->escapeHtml($couponCode)
                    )
                );
            }
            else {
                if ($this->_getQuote()->getCouponCode() !== $couponCode) {
                    throw new \Exception(
                        __(
                            '"%1" coupon code is not valid.',
                            ($couponCode)
                        )
                    );
                }
                else {
                    //$this->messageManager->addSuccess(__('The coupon code has been accepted.'));
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function checkCustomerGroup() {
        $account = $this->getRequest()->getParam('account');
        if (!isset($account['group_id'])) {
            throw new \Exception("Must have param customer_group_id");
        }
        $this->customerSession->setCustomerGroupId($account['group_id']);

        return $this;
    }

    /**
     * Retrieve session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getSession() {
        return $this->getContext()->getObjectManager()->get('Magento\Backend\Model\Session\Quote');
    }

    /**
     * Retrieve quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote() {
        return $this->_getSession()->getQuote();
    }

    /**
     * Initialize order creation session data
     *
     * @return $this
     */
    protected function _initSession() {
        /**
         * Identify customer
         */
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            $this->_getSession()->setCustomerId((int)$customerId);
        }

        /**
         * Identify store
         */
        if ($storeId = $this->getRequest()->getParam('store_id')) {
            $this->_getSession()->setStoreId((int)$storeId);
        }

        /**
         * Identify currency
         */
        if ($currencyId = $this->getRequest()->getParam('currency_id')) {
            $this->_getSession()->setCurrencyId((string)$currencyId);
            $this->_getOrderCreateModel()->setRecollect(true);
        }

        return $this;
    }

    /**
     * Retrieve order create model
     *
     * @return \SM\Sales\Model\AdminOrder\Create
     */
    protected function _getOrderCreateModel() {
        //FIX for magento 2.2.3 : update magento core code in QuoteRepository : $quote->$loadMethod($identifier)->setStoreId($this->storeManager->getStore()->getId());
        $storeID = $this->getRequest()->getParam('store_id');
        if (!!$storeID) {
            $this->storeManager->setCurrentStore($storeID);
        }

        return $this->getContext()->getObjectManager()->get('SM\Sales\Model\AdminOrder\Create');
    }

    /**
     * @return \Magento\Backend\App\Action\Context
     */
    protected function getContext() {
        return $this->context;
    }

    /**
     * @return \SM\XRetail\Helper\DataConfig
     */
    protected function getRetailConfig() {
        return $this->_dataConfig;
    }

    /**
     * @return array
     */
    private function getOutputLoadData() {
        $data = [];
        if ($this->_getQuote()->isVirtual()) {
            $address = $this->_getQuote()->getBillingAddress();
            $totals  = $this->_getQuote()->getBillingAddress()->getTotals();
        }
        else {
            $address = $this->_getQuote()->getShippingAddress();
            $totals  = $this->_getQuote()->getShippingAddress()->getTotals();
        }
        $data['totals'] = [
            'subtotal'                     => $address->getData('subtotal'),
            'subtotal_incl_tax'            => $address->getData('subtotal_incl_tax'),
            'real_tax_for_display_in_xpos' => $address->getTaxAmount() + $address->getDiscountTaxCompensationAmount(),
            'tax_only'                     => $totals['tax']->getData('value'),
            'shipping'                     => $address->getData('shipping_amount'),
            'shipping_incl_tax'            => $address->getData('shipping_incl_tax'),
            'discount'                     => isset($totals['discount']) ? $totals['discount']->getValue() : 0,
            'grand_total'                  => $totals['grand_total']->getData('value'),
            'applied_taxes'                => $this->retailHelper->unserialize($address->getData('applied_taxes')),
            'cart_fixed_rules'             => $address->getData('cart_fixed_rules'),
            'applied_rule_ids'             => $address->getData('applied_rule_ids'),
            'retail_discount_per_item'     => $this->_getQuote()->getData('retail_discount_per_item'),
            'coupon_code'                  => $this->_getQuote()->getCouponCode()
        ];

        $data['items'] = $this->orderHistoryManagement->getOrderItemData($address->getAllItems());

        $data['totals'] = array_map(
            function ($number) {
                if (is_numeric($number)) {
                    return round($number, 2);
                }
                else
                    return $number;
            },
            $data['totals']);

        if ($this->integrateHelperData->isIntegrateRP() && $this->integrateHelperData->isAHWRewardPoints()) {
            $data['reward_point'] = $this->rpIntegrateManagement->getQuoteRPData();
        }

        if ($this->integrateHelperData->isIntegrateGC()) {
            $giftCardRequest = $this->getRequest()->getParam('gift_card');
            if ($giftCardRequest) {
                $data['gift_card'] = $this->gcIntegrateManagement->getQuoteGCData();
            }
            else {
                $data['gift_card'] = [];
            }

        }

        return $data;
    }

    /**
     * Process buyRequest file options of items
     *
     * @param array $items
     *
     * @return array
     */
    protected function _processFiles($items) {
        /* @var $productHelper \Magento\Catalog\Helper\Product */
        $productHelper = $this->getContext()->getObjectManager()->get('Magento\Catalog\Helper\Product');
        foreach ($items as $id => $item) {
            $buyRequest = new \Magento\Framework\DataObject($item);
            $params     = ['files_prefix' => 'item_' . $id . '_'];
            $buyRequest = $productHelper->addParamsToBuyRequest($buyRequest, $params);
            if ($buyRequest->hasData()) {
                $items[$id] = $buyRequest->toArray();
            }
        }

        return $items;
    }

    /**
     * @return $this
     */
    private function transformData() {
        $this->_requestOrderData = $data = $this->getRequest()->getParams();
        $order                   = $this->getRequest()->getParam('order');
        $items                   = $this->getRequest()->getParam('items');

        if (is_array($items)) {
            foreach ($items as $key => $value) {
                if (isset($value['gift_card'])) {
                    if (isset($items[$key]['gift_card']['aw_gc_amount']))
                        $items[$key]['gift_card']['giftcard_amount'] = $items[$key]['gift_card']['aw_gc_amount'];
                    if (isset($items[$key]['gift_card']['aw_gc_custom_amount']))
                        $items[$key]['gift_card']['custom_giftcard_amount'] = $items[$key]['gift_card']['aw_gc_custom_amount'];
                    if (isset($items[$key]['gift_card']['aw_gc_sender_name']))
                        $items[$key]['gift_card']['giftcard_sender_name'] = $items[$key]['gift_card']['aw_gc_sender_name'];
                    if (isset($items[$key]['gift_card']['aw_gc_sender_email']))
                        $items[$key]['gift_card']['giftcard_sender_email'] = $items[$key]['gift_card']['aw_gc_sender_email'];
                    if (isset($items[$key]['gift_card']['aw_gc_recipient_name']))
                        $items[$key]['gift_card']['giftcard_recipient_name'] = $items[$key]['gift_card']['aw_gc_recipient_name'];
                    if (isset($items[$key]['gift_card']['aw_gc_recipient_email']))
                        $items[$key]['gift_card']['giftcard_recipient_email'] = $items[$key]['gift_card']['aw_gc_recipient_email'];
                    if (isset($items[$key]['gift_card']['aw_gc_headline']))
                        $items[$key]['gift_card']['giftcard_headline'] = $items[$key]['gift_card']['aw_gc_headline'];
                    if (isset($items[$key]['gift_card']['aw_gc_message']))
                        $items[$key]['gift_card']['giftcard_message'] = $items[$key]['gift_card']['aw_gc_message'];
                }
            }
            $data['items'] = $items;
        }

        if (isset($order['billing_address']['first_name']))
            $order['billing_address']['firstname'] = $order['billing_address']['first_name'];
        if (isset($order['billing_address']['middle_name']))
            $order['billing_address']['middlename'] = $order['billing_address']['middle_name'];
        if (isset($order['billing_address']['last_name']))
            $order['billing_address']['lastname'] = $order['billing_address']['last_name'];
        if (!is_array($order['billing_address']['street']))
            $order['billing_address']['street'] = [$order['billing_address']['street']];
        if ($order['billing_address']['region_id'] == "*")
            $order['billing_address']['region_id'] = null;


        if (isset($order['shipping_address']['first_name']))
            $order['shipping_address']['firstname'] = $order['shipping_address']['first_name'];
        if (isset($order['shipping_address']['middle_name']))
            $order['shipping_address']['middlename'] = $order['shipping_address']['middle_name'];
        if (isset($order['shipping_address']['last_name']))
            $order['shipping_address']['lastname'] = $order['shipping_address']['last_name'];
        if (!is_array($order['shipping_address']['street']))
            $order['shipping_address']['street'] = [$order['shipping_address']['street']];
        // fix region id for magento 2.2.3 or above
        if ($order['shipping_address']['region_id'] == "*")
            $order['shipping_address']['region_id'] = null;

        if ($this->checkIsRefundToGiftCard()) {
            $refundToGCProductId = $this->gcIntegrateManagement->getRefundToGCProductId();
            $giftCardItems       = [
                'qty'        => 1,
                'product_id' => $refundToGCProductId,
                'product'    => null,
            ];
            if ($this->integrateHelperData->isAHWGiftCardxist() && $this->integrateHelperData->isIntegrateGC()) {
                $giftCardItems['gift_card'] = [
                    'aw_gc_amount'        => "custom",
                    'aw_gc_custom_amount' => $order['payment_data'][0]['refund_amount'] - $data['order']['payment_data'][0]['amount'],
                    'aw_gc_template'      => 'aw_giftcard_email_template',
                    'aw_gc_sender_email'  => $order['payment_data'][0]['sender_email'],
                    'aw_gc_sender_name'   => $order['payment_data'][0]['sender_name'],

                    'aw_gc_recipient_email' => $order['payment_data'][0]['recipient_email'],
                    'aw_gc_recipient_name'  => $order['payment_data'][0]['recipient_name']
                ];
            }
            else if ($this->integrateHelperData->isGiftCardMagento2EE() && $this->integrateHelperData->isIntegrateGC()) {
                $giftCardItems['gift_card'] = [
                    'giftcard_amount'        => "custom",
                    'custom_giftcard_amount' => $order['payment_data'][0]['refund_amount'] - $data['order']['payment_data'][0]['amount'],
                    'giftcard_sender_email'  => $order['payment_data'][0]['sender_email'],
                    'giftcard_sender_name'   => $order['payment_data'][0]['sender_name'],

                    'giftcard_recipient_email' => $order['payment_data'][0]['recipient_email'],
                    'giftcard_recipient_name'  => $order['payment_data'][0]['recipient_name']
                ];
            }
            array_push($data['items'], $giftCardItems);

            $data['order']['payment_data'][0]['isChanging'] = true;
            if ($data['order']['payment_data'][0]['amount'] == 0) {
                $data['order']['payment_data'][0]['amount'] = $data['order']['payment_data'][0]['refund_amount'];
            }
            $this->registry->register(self::USING_REFUND_TO_GIFT_CARD, true);
        }
        else {
            $this->registry->register(self::USING_REFUND_TO_GIFT_CARD, false);
        }

        $data['order'] = $order;

        // gift card data
        $data['items'] = array_map(
            function ($buyRequest) {
                if (isset($buyRequest['gift_card'])) {
                    foreach ($buyRequest['gift_card'] as $key => $value) {
                        if ($key === 'aw_gc_delivery_date' && isset($value['data_date'])) {
                            $buyRequest[$key] = $value['data_date'];
                        }
                        else {
                            $buyRequest[$key] = $value;
                        }
                    }
                }

                return $buyRequest;
            },
            $data['items']);

        $this->getRequest()->setParams($data);

        return $this;
    }

    /**
     *For test
     *
     * @param bool $isExchange
     */
    private function dummyData($isExchange = false) {
        $data = [
            'items'       => [
                // 'q' =>
                //     array(
                //         'qty'                => '1',
                //         // 'custom_price'       => '310',
                //         'use_discount'       => '1',
                //         // 'discount_per_items' => 100,
                //         'product_id'         => 895
                //     ),'q1' =>
                [
                    'qty'               => '2',
                    // 'custom_price'       => '310',
                    //'use_discount' => '1',
                    'discount_per_item' => 10,
                    'product_id'        => 1
                ],
                //[
                //    'qty'                => '1',
                //    // 'custom_price'       => '310',
                //    //'use_discount' => '1',
                //    'discount_per_items' => 20,
                //    'product_id'         => 896
                //],
                //447 =>
                //    [
                //        'qty'                => '1',
                //        'bundle_option_qty'  =>
                //            [
                //                24 => '1',
                //                23 => '1',
                //            ],
                //        'bundle_option'      =>
                //            [
                //                24 => '91',
                //                23 => '88',
                //            ],
                //        'product_id'         => 447,
                //        'discount_per_items' => 50,
                //    ],
                //                    877 =>
                //                        array(
                //                            'qty'             => '1',
                //                            'super_attribute' =>
                //                                array(
                //                                    92  => '20',
                //                                    180 => '78',
                //                                ),
                //                        ),
                //                    555 =>
                //                        array(
                //                            'qty'         => '',
                //                            'super_group' =>
                //                                array(
                //                                    547 => '1',
                //                                    548 => '1',
                //                                    551 => '1',
                //                                ),
                //                        ),
                // 234 =>
                //     array(
                //         'qty'          => '1',
                //         'action'       => '',
                //         'custom_price' => '400',
                //         'use_discount' => '1',
                //         'name'         => 'Test custom sales',
                //         'product_id'   => 'custom_sale'
                //     )
            ],
            'account'     => [
                'group_id' => 2,
                'email'    => 'roni_cost@example.com'
            ],
            'customer_id' => '1',
            'store_id'    => '1',
            'order'       => [
                'billing_address'          => [
                    'firstname'  => 'Veronica2324',
                    'middlename' => 'Bla',
                    'lastname'   => 'Costello',
                    'company'    => 'Taxa',
                    'street'     =>
                        [
                            0 => '6146 Honey Bluff Parkway',
                        ],
                    'city'       => 'Calder',
                    'country_id' => 'US',
                    'region_id'  => '43',
                    'region'     => 'NewJersey',
                    'postcode'   => '49628-7978',
                    'telephone'  => '(555) 229-3326',
                ],
                'shipping_address'         => [
                    'firstname'  => 'Veronica2324',
                    'middlename' => 'Bla',
                    'lastname'   => 'Costello',
                    'company'    => 'Taxa',
                    'street'     =>
                        [
                            0 => '6146 Honey Bluff Parkway',
                        ],
                    'city'       => 'Calder',
                    'country_id' => 'US',
                    'region_id'  => '43',
                    'region'     => 'NewJersey',
                    'postcode'   => '49628-7978',
                    'telephone'  => '(555) 229-3326',
                ],
                'payment_method'           => 'retailmultiple',
                'shipping_method'          => 'retailshipping_retailshipping',
                'shipping_amount'          => 0,
                'shipping_same_as_billing' => 'on',
                'payment_data'             => [
                    'checkmo'        => 123,
                    'cashondelivery' => 345
                ],
                'coupon'                   => [
                    'code' => 75
                ],
                //'whole_order_discount'     => [
                //    'value'         => 50,
                //    'isPercentMode' => false
                //]
            ]
        ];
        if ($isExchange) {
            $data['creditmemo'] = [
                'items'               =>
                    [
                        1128 =>
                            [
                                'qty' => '1',
                            ],
                    ],
                'order_id'            => 281,
                'do_offline'          => '1',
                'comment_text'        => '',
                'shipping_amount'     => '0',
                'adjustment_positive' => '0',
                'adjustment_negative' => '0',
            ];
        }
        $this->getRequest()->setParams($data);
    }

    /**
     * @return $this
     */
    private function checkDiscountWholeOrder() {
        $order = $this->getRequest()->getParam('order');
        if (isset($order['whole_order_discount'])
            && isset($order['whole_order_discount']['value'])
            && $order['whole_order_discount']['value'] > 0
        ) {
            self::$IS_COLLECT_RULE = true;
            if (isset($order['whole_order_discount']['isPercentMode']) && $order['whole_order_discount']['isPercentMode'] !== true) {
                $order['whole_order_discount']['value'] = $order['whole_order_discount']['value'] / $this->getCurrentRate();
            }
            $this->registry->register(self::DISCOUNT_WHOLE_ORDER_KEY, $order['whole_order_discount']);
        }
        else
            $this->registry->register(self::DISCOUNT_WHOLE_ORDER_KEY, false);

        return $this;
    }

    /**
     * @return $this
     */
    private function checkShippingMethod() {
        $order          = $this->getRequest()->getParam('order');
        $shippingAmount = 0;
        if (isset($order['shipping_amount']) && !is_nan($order['shipping_amount'])) {
            $shippingAmount = $order['shipping_amount'];
        }

        $this->registry->register(RetailShipping::RETAIL_SHIPPING_AMOUNT_KEY, $shippingAmount / $this->getCurrentRate());
        $this->registry->register('retail_has_shipment', $this->getRequest()->getParam('retail_has_shipment'));
        self::$FROM_API = true;

        return $this;
    }

    private function getCurrentRate() {
        if ($this->_currentRate === null) {
            $quote              = $this->_getOrderCreateModel()->getQuote();
            $this->_currentRate = $quote->getStore()
                                        ->getBaseCurrency()
                                        ->convert(1, $quote->getQuoteCurrencyCode());
        }

        return $this->_currentRate;
    }

    /**
     * @return $this
     */
    private function checkOfflineMode() {
        if ($this->getRequest()->getParam('is_offline')) {
            self::$IS_COLLECT_RULE = false;
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function checkOutlet() {
        $outletId = $this->getRequest()->getParam('outlet_id');
        if (!!$outletId) {
            $this->registry->unregister('outlet_id');
            $this->registry->register('outlet_id', $outletId);
        }
        else
            throw new \Exception("Please define outlet when save order");

        $this->registry->unregister('retail_note');
        $this->registry->register('retail_note', $this->getRequest()->getParam('retail_note'));

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function checkRegister() {
        // need register id for report
        $registerId = $this->getRequest()->getParam('register_id');
        if (!!$registerId) {
            $this->registry->unregister('register_id');
            $this->registry->register('register_id', $registerId);
        }
        else
            throw new \Exception("Please define register when save order");

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function checkXRefNumCardKnox() {
        // need reference number CardKnox for report
        $xRefNum = $this->getRequest()->getParam('xRefNum');
        if (!!$xRefNum) {
            $this->registry->unregister('xRefNum');
            $this->registry->register('xRefNum', $xRefNum);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function checkOrderCount() {
        $orderCount = $this->getRequest()->getParam('retail_id');
        if (!!$orderCount) {
            $this->registry->unregister('retail_id');
            $this->registry->register('retail_id', $orderCount);
            $count      = intval(substr($orderCount, -8));
            $outletId   = $this->getRequest()->getParam('outlet_id');
            $userId     = $this->getRequest()->getParam('user_id');
            $registerId = $this->getRequest()->getParam('register_id');
            $sellerIds  = $this->getRequest()->getParam('sellers');
            // save cashier to order
            if (!!$userId) {
                $this->registry->unregister('user_id');
                $this->registry->register('user_id', $userId);
            }
            if (!!$sellerIds) {
                $this->registry->unregister('sm_seller_ids');
                $this->registry->register('sm_seller_ids', implode(",", $sellerIds));
            }

            /** @var \SM\XRetail\Model\UserOrderCounter $userOrderCounterModel */
            $userOrderCounterModel = $this->userOrderCounterFactory->create();
            $orderCount            = $userOrderCounterModel->loadOrderCount($outletId, $registerId, $userId);
            $orderCount->setData('order_count', $count)
                       ->setData('user_id', $userId)
                       ->setData('outlet_id', $outletId)
                       ->setData('register_id', $registerId)
                       ->save();
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function checkRetailAdditionData() {
        $retailAdditionData = $this->getRequest()->getParam('retail_addition_data');
        // check has custom sale
        if (isset($retailAdditionData['has_custom_sale']) && $retailAdditionData['has_custom_sale'] == true) {
            $this::$ORDER_HAS_CUSTOM_SALE = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function checkIntegrateRP() {
        if ($this->integrateHelperData->isIntegrateRP()) {
            $this->rpIntegrateManagement->saveRPDataBeforeQuoteCollect($this->getRequest()->getParam('reward_point'));
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function checkIntegrateGC() {
        if ($this->integrateHelperData->isIntegrateGC() && $this->getRequest()->getParam('gift_card')) {
            $this->gcIntegrateManagement->saveGCDataBeforeQuoteCollect($this->getRequest()->getParam('gift_card'));
        }

        return $this;
    }

    protected function isIntegrateGC() {
        if ($this->integrateHelperData->isAHWGiftCardxist()
            && $this->integrateHelperData->isIntegrateGC()
            && $this->getRequest()->getParam(
                'gift_card')) {
            return true;
        }

        return false;
    }

    protected function checkIntegrateWh() {
        if ($this->integrateHelperData->isIntegrateWH()) {
            WarehouseIntegrateManagement::setWarehouseId($this->getRequest()->getParam('warehouse_id'));
        }

        return $this;
    }

    protected function checkIsRefundToGiftCard() {
        $data         = $this->getRequest()->getParams();
        $order        = $data['order'];
        $isRefundByGC = false;

        if (isset($order['payment_data']) && is_array($order['payment_data']) && count($order['payment_data']) == 1) {
            if (isset($order['payment_data'][0]['type']) && $order['payment_data'][0]['type'] == 'refund_gift_card') {
                $isRefundByGC = true;
            }
        }

        if ($this->integrateHelperData->isIntegrateGC() && isset($order['is_exchange']) && $order['is_exchange'] == true && $isRefundByGC
            && is_array(
                $data['items'])
            && count($data['items']) == 0) {
            $this->_isRefundToGC = true;
        }
        else {
            $this->_isRefundToGC = false;
        }

        return $this->_isRefundToGC;
    }

    protected function saveNoteToOrderAlso($order, $comment = null) {
        if ($comment != null || $comment = $this->getRequest()->getParam("retail_note")) {
            /** @var \SM\Sales\Model\AdminOrder\Create $order */
            $order->addStatusHistoryComment($comment)
                  ->setIsCustomerNotified(false)
                  ->setEntityName('order')
                  ->save();
        }
    }
}
