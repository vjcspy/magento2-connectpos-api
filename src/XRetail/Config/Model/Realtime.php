<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 04/06/2018
 * Time: 11:19
 */

namespace SM\XRetail\Config\Model;
class Realtime implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('Immediately'),
                'value' => 'immediately',
            ],
            [
                'label' => __('Cronjob'),
                'value' => 'cronjob',
            ],
        ];
        return $options;
    }
}