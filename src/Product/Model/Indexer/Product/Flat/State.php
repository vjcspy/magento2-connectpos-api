<?php
/**
 * Created by PhpStorm.
 * User: xuantung
 * Date: 11/13/18
 * Time: 10:24 AM
 */

namespace SM\Product\Model\Indexer\Product\Flat;


class State
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    )
    {
        $this->registry = $registry;
    }

    public function afterIsFlatEnabled(\Magento\Catalog\Model\Indexer\Product\Flat\State $subject) {
        if($this->registry->registry('disableFlatProduct'))
            return false;
    }
}