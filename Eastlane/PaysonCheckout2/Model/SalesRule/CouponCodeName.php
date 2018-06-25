<?php

namespace Eastlane\PaysonCheckout2\Model\SalesRule;
/**
 * Class CouponCodeName
 *
 * @package Eastlane\PaysonCheckout2\Model\SalesRule
 */

class CouponCodeName
{
    /**
     * @param $ids
     * @return string
     */
    public function getCouponLabel($ids)
    {
        $rulesIds = explode(',', $ids);
        if(!empty($rulesIds)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $rules = $objectManager->create('Magento\SalesRule\Model\Rule')->getCollection();
            $rules->addFieldToFilter('rule_id', ['in' => $rulesIds]);
            $rules->addFieldToFilter('is_active', 1);
            $couponName = [];
            foreach ($rules as $rule)
            {
                $couponName[] =   $rule->getName();
            }
            return  implode($couponName, ' & ');
        }
    }

    /**
     * @param $ids
     * @return string
     */
    public function getCouponCode($ids)
    {
        $rulesIds = explode(',', $ids);
        if(!empty($rulesIds)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $rules = $objectManager->create('Magento\SalesRule\Model\Rule')->getCollection();
            $rules->addFieldToFilter('rule_id', ['in' => $rulesIds]);
            $rules->addFieldToFilter('is_active', 1);
            $couponName = [];
            foreach ($rules as $rule)
            {
                if($rule->getCode()) {
                    $couponName[] =   $rule->getCode();
                }

            }
            return  implode($couponName);
        }
    }
}
