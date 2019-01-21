<?php

namespace SM\Tls\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

abstract class Accounting extends Action {

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ACTION_RESOURCE = 'SM_Tls::accounting';

    /**
     * Data repository
     *
     * @var DataRepositoryInterface
     */
    protected $dataRepository;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Result Page Factory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Result Forward Factory
     *
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Data constructor.
     *
     * @param Registry                $registry
     * @param DataRepositoryInterface $dataRepository
     * @param PageFactory             $resultPageFactory
     * @param ForwardFactory          $resultForwardFactory
     * @param Context                 $context
     */
    public function __construct(
        Registry $registry,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Context $context
    ) {
        $this->coreRegistry         = $registry;
        $this->resultPageFactory    = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }
}
