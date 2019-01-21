<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 20/03/2017
 * Time: 18:47
 */

namespace SM\Integrate\RewardPoint\Contract;


use Magento\Framework\ObjectManagerInterface;

/**
 * Class AbstractRPIntegrate
 *
 * @package SM\Integrate\RewardPoint\Contract
 */
abstract class AbstractRPIntegrate {

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * AbstractRPIntegrate constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieve session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function getSession() {
        return $this->objectManager->get('Magento\Backend\Model\Session\Quote');
    }

    /**
     * Retrieve quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote() {
        return $this->getSession()->getQuote();
    }
}