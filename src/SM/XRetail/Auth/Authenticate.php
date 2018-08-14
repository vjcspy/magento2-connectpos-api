<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 7/4/17
 * Time: 2:02 PM
 */

namespace SM\XRetail\Auth;

use SM\XRetail\Controller\V1\Xretail;

class Authenticate {

    private $_configuration;
    const PATH_KEY                   = 'core/config/key_x';
    const HEADER_AUTHENTICATION_CODE = 'Authorization-Code';
    const HEADER_KEY_NAME            = 'Black-Hole';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->_configuration = $scopeConfig;
        $this->encryptor      = $encryptor;
    }

    /**
     * @param \SM\XRetail\Controller\V1\Xretail $controller
     *
     * @return $this
     * @throws \Exception
     */
    public function authenticate(Xretail $controller) {
        if ($controller->getPath() === 'debug') {
            return $this;
        }

        // Deprecated authenticate with fix token key
        //if ($controller->getRequest()->getParam('token_key')
        //    && $controller->getRequest()->getParam('token_key') === base64_encode('mr.vjcspy@gmail.com')) {
        //    return $this;
        //}
        else if ($controller->getRequest()->getParam('__token_key')
                 && $controller->getRequest()->getParam('__token_key') === base64_encode(
                $this->encryptor->decrypt(
                    $this->_configuration->getValue("xpos/general/retail_license")))) {
            return $this;
        }else {
            $controller->setStatusCode(403);
            throw new \Exception('Forbidden');
        }

        return $this;
    }

    public function getBlackHole(SM_XRetail_V1Controller $controller) {
        if (!$controller->getRequest()->getHeader(self::HEADER_AUTHENTICATION_CODE)) {
            throw new \Exception('Forbidden');
        }

        //
        if (
        $this->callLicenseApi($controller->getRequest()->getHeader(self::HEADER_AUTHENTICATION_CODE))
        ) {
            $w = md5(microtime());
            $this->_configuration->getValue(self::PATH_KEY, $w);

            return [
                'Black-Hole' => $w,
            ];
        }
        else {
            $controller->setStatusCode(403);
            throw new \Exception('Forbidden');
        }

    }

    private function callLicenseApi($licenseId) {
        return true;
    }

}
