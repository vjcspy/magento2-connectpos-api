<?php

namespace SM\XRetail\Controller\V1;

use SM\XRetail\Auth\Authenticate;
use SM\XRetail\Controller\Contract\ApiAbstract;

class Xretail extends ApiAbstract {

    private $authenticate;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \SM\XRetail\Model\Api\Configuration $configuration,
        \Magento\PageCache\Model\Config $config,
        Authenticate $authenticate
    ) {
        parent::__construct($context, $scopeConfig, $configuration, $config);
        $this->authenticate = $authenticate;
    }

    public function execute() {
        try {
            // authenticate
            //$this->authenticate->authenticate($this);

            // communicate with api before
            $this->dispatchEvent('rest_api_before', ['apiController' => $this]);
            // call service
            $this->setOutput(
                call_user_func_array(
                    [$this->getService(), $this->getFunction()],
                    $this->getRequest()->getParams()
                )
            );
            // communicate with api after
            $this->dispatchEvent('rest_api_after', ['apiController' => $this]);

            // output data
            return $this->jsonOutput();

        }
        catch (\Exception $e) {
            return $this->outputError($e->getMessage(), $this->getStatusCode());
        }
    }
}
