<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 07/12/2016
 * Time: 09:19
 */

namespace SM\Payment\Block\Info;


use Magento\Framework\View\Element\Template;

/**
 * Class RetailMultiple
 *
 * @package SM\Payment\Block\Info
 */
class RetailMultiple extends \Magento\Payment\Block\Info {

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * RetailMultiple constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    /**
     * @param $code
     * @param $field
     *
     * @return mixed
     */
    public function getPaymentMethodConfigData($code, $field) {
        $path = 'payment/' . $code . '/' . $field;

        return $this->_scopeConfig->getValue($path, 'default', $this->_multiplePayment['store_id']);
    }

    /**
     * @var
     */
    protected $_multiplePayment;

    /**
     * @var string
     */
    protected $_template = 'SM_Payment::info/retailmultiple.phtml';

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getMultiplePaymentData() {
        if (is_null($this->_multiplePayment)) {
            $this->_convertAdditionalData();
        }

        return $this->_multiplePayment;
    }

    /**
     * @param $price
     *
     * @return float
     */
    public function formatPrice($price) {
        $order      = $this->orderRepository->get($this->getInfo()->getEntityId());  //load order by order id
        return $this->priceCurrency->format(
            $price,
            $includeContainer = true,
            $precision = 2,
            $scope = null,
            $currency = $this->_storeManager->getStore($order->getStoreId())->getCurrentCurrencyCode()
        );
    }

    /**
     * @return $this
     */
    protected function _convertAdditionalData() {
        $this->_multiplePayment = json_decode($this->getInfo()->getAdditionalInformation('split_data'), true);
        $this->_multiplePayment = array_filter(
            $this->_multiplePayment,
            function ($val) {
                return is_array($val);
            });

        return $this;
    }

    /**
     * @return array
     */
    public function filterFields() {
        return ['store_id', 'method_title'];
    }

    /**
     * @return string
     */
    public function toPdf() {
        $this->setTemplate('SM_Payment::info/pdf/retailmultiple.phtml');

        return $this->toHtml();
    }
}
