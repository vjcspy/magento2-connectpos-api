<?php

namespace SM\XRetail\Observer;

use SM\Performance\Gateway\Sender;
use Magento\Framework\App\Config\ScopeConfigInterface;
class ChangeConfigXposAfter implements \Magento\Framework\Event\ObserverInterface {

    protected static $instance;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagement;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    private $helper;
    /**
     * @var string
     */
    protected $_licenseKey;

    /**
     * @var string
     */
    protected $_baseUrl;

    protected $_apiVersion;

    protected $sender;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManagement,
        \Psr\Log\LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \SM\XRetail\Helper\Data $helper,
        \SM\Performance\Gateway\Sender $sender
    ) {
        $this->encryptor       = $encryptor;
        $this->scopeConfig     = $scopeConfig;
        $this->logger          = $logger;
        $this->storeManagement = $storeManagement;
        $this->helper          = $helper;
        $this->sender          = $sender;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        if (is_null($this->_licenseKey)) {
            $this->_licenseKey = $this->encryptor->decrypt($this->scopeConfig->getValue("xpos/general/retail_license"));
        }

        if (is_null($this->_baseUrl)) {
            $this->_baseUrl = rtrim($this->storeManagement->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true), '/');
        }
        if (is_null($this->_apiVersion)) {
            $this->_apiVersion = $this->helper->getCurrentVersion();
        }
        $data       = [
            'url'         => $this->_baseUrl,
            'license_key' => $this->_licenseKey,
            'api_version' => $this->_apiVersion
        ];
        $this->sender->sendPostViaSocket($this->getBaseUrl(),$data);
    }

    protected function getBaseUrl() {
        return Sender::$CLOUD_URL."/methods/client.save_api_version";
    }

}

?>