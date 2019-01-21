<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/5/17
 * Time: 10:03 AM
 */

namespace SM\Performance\Gateway;


/**
 * Class Sender
 *
 * @package SM\Performance\Gateway
 */
use Magento\Framework\App\Config\ScopeConfigInterface;
use SM\Performance\Helper\CacheKeeper;

/**
 * Class Sender
 *
 * @package SM\Performance\Gateway
 */
class Sender {
    static $CLOUD_URL = "http://cloud.connectpos.com";
    //static $CLOUD_URL = "http://cloud.local:2005";
    //static $CLOUD_URL = "http://accounts.product.smartosc.com:2005";

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
    /**
     * @var string
     */
    protected $_licenseKey;

    /**
     * @var string
     */
    protected $_baseUrl;

    /**
     * RealtimeManager constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagement
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManagement,
        \Psr\Log\LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->encryptor       = $encryptor;
        $this->scopeConfig     = $scopeConfig;
        $this->logger          = $logger;
        $this->storeManagement = $storeManagement;
    }

    /**
     * @param $entity
     * @param $entityId
     * @param $typeChange
     */
    public function sendMessage($entity, $entityId, $typeChange) {
        if (is_null($this->_licenseKey)) {
            $this->_licenseKey = $this->encryptor->decrypt($this->scopeConfig->getValue("xpos/general/retail_license"));
        }
        if (is_null($this->_baseUrl)) {
            $this->_baseUrl = $this->storeManagement->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true);
        }
        if (!!$this->_licenseKey && !!$this->_baseUrl) {
            $baseUrl = $this->storeManagement->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true);
            $param   = [
                'license'    => $this->_licenseKey,
                'base_url'   => $baseUrl,
                'data'       => [
                    'entity'      => $entity,
                    'entity_id'   => $entityId,
                    'type_change' => $typeChange
                ],
                'cache_time' => intval(microtime(true)*1000),
                'created_at' => date("Y-m-d H:i:s")
            ];
            $this->sendPostViaSocket($this->getBaseUrl(), $param);
        }
    }

    /**
     * @param $data
     */
    public function sendMessages($data) {
        if (is_null($this->_licenseKey)) {
            $this->_licenseKey = $this->encryptor->decrypt($this->scopeConfig->getValue("xpos/general/retail_license"));
        }
        if (is_null($this->_baseUrl)) {
            $this->_baseUrl = $this->storeManagement->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true);
        }
        $checkExisted = [];
        if (!!$this->_licenseKey && !!$this->_baseUrl) {
            $batch = [];
            foreach ($data as $datum) {
                $checkKey = $datum['entity'] . '_' . $datum['entity_id'] . '_' . $datum['type_change'];
                if (in_array($checkKey, $checkExisted)) {
                    continue;
                }

                $param          = [
                    'license'    => $this->_licenseKey,
                    'base_url'   => $this->_baseUrl,
                    'data'       => [
                        'entity'      => $datum['entity'],
                        'entity_id'   => $datum['entity_id'],
                        'type_change' => $datum['type_change']
                    ],
                    'cache_time' => CacheKeeper::getCacheTime(),
                    'created_at' => date("Y-m-d H:i:s")
                ];
                $batch[]        = $param;
                $checkExisted[] = $checkKey;
            }

            $this->sendPostViaSocket($this->getBaseUrl(), ["batch" => $batch]);
        }

    }

    public function getDataToSendViaRawSender($data) {
        if (is_null($this->_licenseKey)) {
            $this->_licenseKey = $this->encryptor->decrypt($this->scopeConfig->getValue("xpos/general/retail_license"));
        }
        if (is_null($this->_baseUrl)) {
            $this->_baseUrl = $this->storeManagement->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true);
        }
        $checkExisted = [];
        if (!!$this->_licenseKey && !!$this->_baseUrl) {
            $batch = [];
            foreach ($data as $datum) {
                $checkKey = $datum['entity'] . '_' . $datum['entity_id'] . '_' . $datum['type_change'];
                if (in_array($checkKey, $checkExisted)) {
                    continue;
                }

                $param          = [
                    'license'    => $this->_licenseKey,
                    'base_url'   => $this->_baseUrl,
                    'data'       => [
                        'entity'      => $datum['entity'],
                        'entity_id'   => $datum['entity_id'],
                        'type_change' => $datum['type_change']
                    ],
                    'cache_time' => CacheKeeper::getCacheTime(),
                    'created_at' => date("Y-m-d H:i:s")
                ];
                $batch[]        = $param;
                $checkExisted[] = $checkKey;
            }

            return $this->getBaseUrl() . ' ' . $this->_licenseKey . ' ' . json_encode(["batch" => $batch]);
        }

        return null;
    }

    /**
     * @param $url
     * @param $params
     *
     * @return bool
     */
    public function sendPostViaSocket($url, $params) {
        $content = json_encode($params);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            ["Content-type: application/json"]);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

        $json_response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status != 200) {
            $this->logger->debug(
                "Error: call to URL $url failed with status $status, response $json_response, curl_error "
                . curl_error($curl)
                . ", curl_errno "
                . curl_errno($curl));
        }

        curl_close($curl);

        return $response = json_decode($json_response, true);
    }

    protected function getBaseUrl() {
        return self::$CLOUD_URL."/methods/client.trigger_realtime";
        //return "http://localhost:2005/methods/client.trigger_realtime";
        //return "http://xcloud.smartosc.com:2005/methods/client.trigger_realtime";
    }
}
