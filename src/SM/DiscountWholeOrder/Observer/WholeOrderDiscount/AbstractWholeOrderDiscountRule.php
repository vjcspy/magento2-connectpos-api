<?php

namespace SM\DiscountWholeOrder\Observer\WholeOrderDiscount;

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 12/10/2016
 * Time: 15:37
 */
abstract class AbstractWholeOrderDiscountRule {

    const RULE_ID = 99999;
    private $_value;


    /**
     * @param $data
     *
     * @return array
     * @throws \Exception
     */
    public function getRule($data) {
        if (!isset($data['value']))
            throw new \Exception("Can't get percent value");
        $this->setValue($data['value']);

        return [];
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value) {
        $this->_value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->_value;
    }

    protected function fixCompatibleMage22($rule) {
        if (class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
            $rule['conditions_serialized']
                = '{"type":"Magento\\\\SalesRule\\\\Model\\\\Rule\\\\Condition\\\\Combine","attribute":null,"operator":null,"value":"1","is_value_processed":null,"aggregator":"all"}';
            $rule['actions_serialized']
                = '{"type":"Magento\\\\SalesRule\\\\Model\\\\Rule\\\\Condition\\\\Product\\\\Combine","attribute":null,"operator":null,"value":"1","is_value_processed":null,"aggregator":"all"}';
        }

        return $rule;
    }
}