<?php

namespace SM\CardKnox\Repositories;

use SM\XRetail\Repositories\Contract\ServiceAbstract;

/**
 * Class CardKnoxManagement
 *
 * @package SM\CardKnox\Repositories
 */
class CardKnoxManagement extends ServiceAbstract {

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    protected $_transformCardKnoxData
        = [
            "xKey"             => "xKey",
            "xSoftwareName"    => "xSoftwareName",
            "xSoftwareVersion" => "xSoftwareVersion",
            "xVersion"         => "xVersion",
            "xCommand"         => "xCommand",
            "xCardNum"         => "xCardNum",
            "xMagstripe"       => "xMagstripe",
            "xName"            => "xName",
            "xExp"             => "xExp",
            "xCVV"             => "xCVV"
        ];

    /**
     * OrderHistoryManagement constructor.
     * @param \Magento\Framework\App\RequestInterface                         $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                                   $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    public function processCardKnox() {
        $cardKnoxData = $this->getRequestData();

        $this->storeManager->setCurrentStore($this->getRequest()->getParam('___store'));

        $isRefunding = isset($cardKnoxData['is_purchase']) && $cardKnoxData['is_purchase'] === 0 ? true : false;

        $result_array = [];
        $data = [];
        foreach ($this->_transformCardKnoxData as $k => $v) {
            $data[$k] = $cardKnoxData[$v];
        }
        if (isset($cardKnoxData['payment_data'])) {
            $data["xKey"]             = $cardKnoxData['payment_data']['xKey'];
            $data["xSoftwareName"]    = $cardKnoxData['payment_data']['xSoftwareName'];
            $data["xSoftwareVersion"] = $cardKnoxData['payment_data']['xSoftwareVersion'];
        }
        $data["xAmount"]           = $this->convertPrice($cardKnoxData['amount']);
        $data["xAllowPartialAuth"] = "True";
        $data["xAllowDuplicate"]   = "True";
        $data["xCommand"]          = "cc:sale";
        $data["xVersion"]          = "4.5.5";

        //if ($data["xMagstripe"] === null) {
        //    $data["xMagstripe"] = "%B4444333322221111^TEST CARD/VISA^4912101123456789?;4444333322221111=4912101123456789?";
        //}

        if ($cardKnoxData['isSwipeCard'] === true) {
            $data["xCardNum"] = null;
            $data["xName"] = null;
            $data["xExp"] = null;
            $data["xCVV"] = null;
        } else {
            $data["xMagstripe"] = null;
        }

        if ($isRefunding === true) {
            $data['xRefNum'] = $cardKnoxData['xRefNum'];
            $data["xCommand"] = "cc:voidrefund";
            $data["xAmount"] = null;
            $data["xMagstripe"] = null;
        }

        $data = $this->buildQuery($data);
        $ch = curl_init("https://x1.cardknox.com/gateway");

        if(!is_resource($ch))
        {
            echo "Error: Unable to initialize CURL ($ch)";
            exit;
        }
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $raw_result = curl_exec($ch);
        if(curl_error($ch) != "")
        {
            echo curl_error($ch);
        }
        elseif(!strlen($raw_result))
        {
            echo "Error reading from card processing gateway. Please contact the merchant to verify whether transaction has been processed.";
            curl_close($ch);
            exit;
        }
        elseif($raw_result == false)
        {
            echo "Blank response from card processing gateway.";
            curl_close($ch);
            exit;
        }
        else
        {
            // SUCCESS
            curl_close($ch);
            // result will be on the last line of the return
            $tmp = explode("\n",$raw_result);
            $result_string = $tmp[count($tmp)-1];
            parse_str($result_string, $result_array);
        }

        return $result_array;
    }

    private function buildQuery($data) {
        if (function_exists('http_build_query') && ini_get('arg_separator.output') == '&') return http_build_query($data);
        $tmp = [];
        foreach ($data as $key => $val) $tmp[] = rawurlencode($key) . '=' . rawurlencode($val);

        return implode('&', $tmp);
    }

    /**
     * @param $amount
     *
     * @return float
     */
    public function convertPrice($amount = 0, $currency = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceCurrencyObject = $objectManager->get('Magento\Framework\Pricing\PriceCurrencyInterface'); //instance of PriceCurrencyInterface
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface'); //instance of StoreManagerInterface
        if ($this->storeManager == null) {
            $this->storeManager = $storeManager->getStore()->getStoreId(); //get current store id if store id not get passed
        }
        $rate = $priceCurrencyObject->convert($amount, $this->storeManager, $currency); //it return price according to current store from base currency

        //If you want it in base currency then use:
        $rate = $this->priceCurrency->convert($amount, $this->storeManager) / $amount;
        $amount = $amount / $rate;

        return $priceCurrencyObject->round($amount);//You can round off to it or you can return it in its original form
    }
}
