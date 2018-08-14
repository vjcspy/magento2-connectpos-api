<?php
/*
 * Turiknox_Sample

 * @category   Turiknox
 * @package    Turiknox_Sample
 * @copyright  Copyright (c) 2017 Turiknox
 * @license    https://github.com/Turiknox/magento2-sample-uicomponent/blob/master/LICENSE.md
 * @version    1.0.0
 */

namespace SM\Tls\Controller\Adminhtml\Accounting;


use SM\Tls\Controller\Adminhtml\Accounting;

class Index extends Accounting {

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Accounting Journal Report'));

        return $resultPage;
    }
}
