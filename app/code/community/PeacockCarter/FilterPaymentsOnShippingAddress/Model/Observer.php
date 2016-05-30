<?php

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
