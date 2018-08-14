<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 7/4/17
 * Time: 3:54 PM
 */

namespace SM\XRetail\Plugin;


class DisableRetailFullPageCache {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    public function aroundProcess(
        \Magento\Framework\App\PageCache\Kernel $subject,
        \Closure $proceed,
        \Magento\Framework\App\Response\Http $response
    ) {
        $path = $this->request->getPathInfo();
        if (strpos($path, '/xrest/v1/xretail') !== FALSE) {
            return;
        }
        else {
            $proceed($response);
        }
    }
}