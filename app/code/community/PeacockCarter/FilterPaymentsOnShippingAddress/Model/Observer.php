<?php

/**
 * PeacockCarter
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    PeacockCarter
 * @package     FilterPaymentsOnShippingAddress
 * @copyright  Copyright (c) 2016 PeacockCarter Ltd
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Peacock_FilterPaymentMethodsOnShippingAddress_Model_Observer
{
    /**
     * @array
     */
    private $_allowedCountries;

    /**
     * @var Mage_Sales_Model_Quote
     */
    private $quote;

    /**
     * @param Varien_Event_Observer $observer
     */
    public function shippingBasedPaymentMethod(Varien_Event_Observer $observer)
    {
        $paymentMethodCode = $this->getPaymentMethodCode($observer);

        if (! $this->isPaymentFilterEnabled($paymentMethodCode) || ! $this->doesQuoteExist($observer)) {

            return;
        }

        $this->setAllowedCountriesForMethod($paymentMethodCode);

        $this->setMethodVisibility($observer);
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return mixed
     */
    private function getPaymentMethodCode(Varien_Event_Observer $observer)
    {
        $MethodInstance    = $observer->getEvent()->getMethodInstance();
        $paymentMethodCode = $MethodInstance->getCode();

        return $paymentMethodCode;
    }

    /**
     * @param $paymentMethodCode
     *
     * @return bool
     */
    private function isPaymentFilterEnabled($paymentMethodCode)
    {
        $enabled = Mage::getStoreConfig('payment/' . $paymentMethodCode . '/shippingbased', Mage::app()->getStore());

        return isset($enabled) && $enabled === "1";
    }

    /**
     * @param $paymentMethodCode
     */
    protected function setAllowedCountriesForMethod($paymentMethodCode)
    {
        $shippingCountries       = Mage::getStoreConfig('payment/' . $paymentMethodCode . '/specificshippingcountry', Mage::app()->getStore());
        $this->_allowedCountries = explode(',', $shippingCountries);
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return bool
     */
    private function doesQuoteExist(Varien_Event_Observer $observer)
    {
        $this->quote = $observer->getEvent()->getQuote();

        return $this->quote && $this->quote->getId();
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    private function setMethodVisibility(Varien_Event_Observer $observer)
    {
        $result = $observer->getEvent()->getResult();

        $result->isAvailable = $this->isCountryAllowed();
    }

    /**
     * @return bool
     */
    private function isCountryAllowed()
    {
        $ShippingAddress = $this->quote->getShippingAddress();
        $country_id      = $ShippingAddress->getCountryId();

        return in_array($country_id, $this->_allowedCountries);
    }
}
