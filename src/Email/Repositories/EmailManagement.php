<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/3/17
 * Time: 11:12 AM
 */

namespace SM\Email\Repositories;


use SM\XRetail\Repositories\Contract\ServiceAbstract;

/**
 * Class EmailManagement
 *
 * @package SM\Email\Repositories
 */
class EmailManagement extends ServiceAbstract {


    /**
     * @var \SM\Email\Helper\EmailSender
     */
    private $emailSender;

    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SM\Email\Helper\EmailSender $emailSender
    ) {
        parent::__construct($requestInterface, $dataConfig, $storeManager);
        $this->emailSender = $emailSender;
    }

    public function sendReceipt() {
        $template = $this->getRequest()->getParam('template');
        $email    = $this->getRequest()->getParam('email');
        $name     = $this->getRequest()->getParam('name');
        $this->storeManager->setCurrentStore($this->getRequest()->getParam('store_id'));
        if (!is_null($template) && !is_null($email) && !is_null($name)) {
            $this->emailSender->sendReceipt(['template' => $template], null, ['email' => $email, 'name' => $name]);
        }
        else
            throw new \Exception("Require param template, email, name");
    }
}