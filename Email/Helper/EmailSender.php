<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/3/17
 * Time: 10:41 AM
 */

namespace SM\Email\Helper;


use Magento\Sales\Model\Order\Email\Container\OrderIdentity;

/**
 * Class EmailSender
 *
 * @package SM\Email
 */
class EmailSender extends \Magento\Framework\App\Helper\AbstractHelper {

    /**
     *
     */
    const XML_PATH_EMAIL_TEMPLATE_FIELD = 'xpos/email/pos_receipt_template';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var string
     */
    protected $temp_id;

    /**
     * EmailSender constructor.
     *
     * @param \Magento\Framework\App\Helper\Context              $context
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->_scopeConfig = $context;
        parent::__construct($context);
        $this->_storeManager     = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
    }

    /**
     * Return store configuration value of your template field that which id you set for template
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    protected function getConfigValue($path, $storeId) {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore() {
        return $this->_storeManager->getStore();
    }

    /**
     * Return template id according to store
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath) {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * @param $emailTemplateVariables
     * @param $senderInfo
     * @param $receiverInfo
     *
     * @return $this
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo) {
        $this->_transportBuilder->setTemplateIdentifier($this->temp_id)
                                ->setTemplateOptions(
                                    [
                                        'area'  => \Magento\Framework\App\Area::AREA_ADMINHTML, /* here you can defile area and
                                                                                 store of template for which you prepare it */
                                        'store' => $this->_storeManager->getStore()->getId(),
                                    ]
                                )
                                ->setTemplateVars($emailTemplateVariables)
                                ->setFrom($senderInfo)
                                ->addTo($receiverInfo['email'], $receiverInfo['name']);

        return $this;
    }

    /**
     * @param $emailTemplateVariables
     * @param $senderInfo
     * @param $receiverInfo
     */
    public function sendReceipt($emailTemplateVariables, $senderInfo = null, $receiverInfo) {
        //try {
            if (is_null($senderInfo)) {
                // $senderInfo = $this->getConfigValue(OrderIdentity::XML_PATH_EMAIL_IDENTITY, $this->getStore()->getId());
                $senderInfo = array('email' => $this->getConfigValue('trans_email/ident_sales/email', $this->getStore()->getId()),
                    'name' => $this->getConfigValue('trans_email/ident_sales/name', $this->getStore()->getId()));
            }
            $this->temp_id = "xpos_send_receipt";
            $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        //}
        //catch (\Exception $e) {
        //    $this->logger->critical($e);
        //}
    }
}