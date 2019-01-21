<?php

namespace SM\XRetail\Controller\V1;

use SM\XRetail\Auth\Authenticate;
use SM\XRetail\Controller\Contract\ApiAbstract;

/**
 * Class Xretail
 * Magento 2.3 implement new CORS site check. But we don't need implement the new interface here. We already support it on client by adding ajax tag.
 *
 * @package SM\XRetail\Controller\V1
 */
class Xretail extends ApiAbstract {

    /**
     * @var \SM\XRetail\Auth\Authenticate
     */
    private $authenticate;

    /**
     * Xretail constructor.
     *
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \SM\XRetail\Model\Api\Configuration                $configuration
     * @param \Magento\PageCache\Model\Config                    $config
     * @param \SM\XRetail\Auth\Authenticate                      $authenticate
     */
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

    /**
     * @return \Magento\Framework\App\Response\Http|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        try {
            // authenticate
            $this->authenticate->authenticate($this);

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
