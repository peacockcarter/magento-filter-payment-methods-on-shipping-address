# Filter Magento payment options by shipping address

A Magento Enterprise client based in the Middle East requested that the "Cash on delivery" payment method be made available to any billing address but be limited to just one shipping country.
Cash on delivery is a very popular payment method in that part of the world, though the client only wanted to offer this in the UAE, and not any of the surrounding countries they operate in.
This site sells computer games and a large proportion of their clients are under 18 and therefore don't have their own credit cards; our client found that this customer group would therefore often use their parents' work address as the billing address.
As such, this meant that the payment address was often overseas - the USA, or other countries in the region. 

Peacock Carter developed bespoke functionality to allow Magento to handle this, and we have extended it for release to the wider Magento community.

To implement this feature, we added a field to the cash on delivery configuration in payment methods. You can limit by shipping address by using the adminhtml/system_config_source_yesno method to create a yes/no select.

```xml
<config>
    <sections>
        <payment>
            <groups>
                <cashondelivery>
                    <fields>
                        <shippingbased translate="label">
                            <label>Limit by Shipping Address</label>
                            <frontend_type>select</frontend_type>
                            <source_model>adminhtml/system_config_source_yesno</source_model>
                            <sort_order>52</sort_order>
                            <show_in_default>1</show_in_default>
                            <show_in_website>1</show_in_website>
                            <show_in_store>1</show_in_store>
                        </shippingbased>
                        ...
```

If this value is set to "yes", then a multiselect of countries - "Allowed Shipping Countries" - would be shown on the individual product.

```xml
<config>
    <sections>
        <payment>
            <groups>
                <cashondelivery>
                    <fields>
                        <shippingbased translate="label">
                            ...
                        </shippingbased>
                        <specificshippingcountry translate="label">
                            <label>Allowed Shipping Countries</label>
                            <frontend_type>multiselect</frontend_type>
                            <sort_order>53</sort_order>
                            <source_model>adminhtml/system_config_source_country</source_model>
                            <show_in_default>1</show_in_default>
                            <show_in_website>1</show_in_website>
                            <show_in_store>0</show_in_store>
                            <can_be_empty>1</can_be_empty>
                            <depends>
                                <shippingbased>1</shippingbased>
                            </depends>
                        </specificshippingcountry>
                        ...
```
 
With this data saved into the database it was possible to use the "payment_method_is_active" event in Magento to filter the payment options based on the shipping address.

The basic configuration XML for this module is as follows;

* Define the module and version
* Let Magento know where the models are located
* Define which class and method to fire when this event occurs

```xml
<config>
    <modules>
        <PeacockCarter_FilterPaymentMethodsOnShippingAddress>
            <version>1.0.0</version>
        </PeacockCarter_FilterPaymentMethodsOnShippingAddress>
    </modules>
    <global>
        <models>
            <peacockcarter_filterpaymentmethodsonshippingaddress>
                <class>PeacockCarter_FilterPaymentMethodsOnShippingAddress_Model</class>
            </peacockcarter_filterpaymentmethodsonshippingaddress>
        </models>
        <events>
            <payment_method_is_active>
                <observers>
                    <disable_cod_on_shipping_address>
                        <class>PeacockCarter_FilterPaymentMethodsOnShippingAddress_Model_Observer</class>
                        <method>shippingBasedPaymentMethod</method>
                    </disable_cod_on_shipping_address>
                </observers>
            </payment_method_is_active>
        </events>
    </global>
</config>
```

When the event is fired we get the payment method code. Check if it is enabled and that the quote exists.
Then we get the allowed countries and set the method visibility, true or false.

```php
class PeacockCarter_FilterPaymentMethodsOnShippingAddress_Model_Observer
{
    ...

    public function shippingBasedPaymentMethod(Varien_Event_Observer $observer)
    {
        $paymentMethodCode = $this->getPaymentMethodCode($observer);
    
        if (! $this->isPaymentFilterEnabled($paymentMethodCode) || ! $this->doesQuoteExist($observer)) {
    
            return;
        }
    
        $this->setAllowedCountriesForMethod($paymentMethodCode);
    
        $this->setMethodVisibility($observer);
    }
    
    ...
}
```

Once I had this working we refactored the code and made it more generic so that it would work for any of the core payment methods.
Core Magento payment methods are:

* Cash On Delivery (cashondelivery)
* Saved Credit Card (ccsave)
* Cheque/mail order (checkmo)
* Free (free)
* Purchase order (purchaseorder)
* Bank transfer (banktransfer)

You could argue that Authorise.net and Moneybookers are also core payment methods. 
These are, however, separate modules and can be disabled independently of these core methods, and for this reason we decided not to include them in this module.
It is very simple to add them, or any other payment method, to this module.

* Create your own module.
* Create a system.xml file with the following content;

```
<?xml version="1.0" encoding="UTF-8"?>
<config>
    <sections>
        <payment>
            <groups>
                <authorizenet>
                    <fields>
                        <shippingbased translate="label">
                            <label>Limit by Shipping Address</label>
                            <frontend_type>select</frontend_type>
                            <source_model>adminhtml/system_config_source_yesno</source_model>
                            <sort_order>52</sort_order>
                            <show_in_default>1</show_in_default>
                            <show_in_website>1</show_in_website>
                            <show_in_store>1</show_in_store>
                        </shippingbased>
                        <specificshippingcountry translate="label">
                            <label>Allowed Shipping Countries</label>
                            <frontend_type>multiselect</frontend_type>
                            <sort_order>53</sort_order>
                            <source_model>adminhtml/system_config_source_country</source_model>
                            <show_in_default>1</show_in_default>
                            <show_in_website>1</show_in_website>
                            <show_in_store>0</show_in_store>
                            <can_be_empty>1</can_be_empty>
                            <depends>
                                <shippingbased>1</shippingbased>
                            </depends>
                        </specificshippingcountry>
                    </fields>
                </authorizenet>
            </groups>
        </payment>
    </sections>
</config>
```

For other payment methods replace **<authorizenet>** with the payment method code. If in doubt these codes can be found in the core_config_data table in the database.
Search for a path like "%payment/%" and you'll find entries such as **payment/braintree_basic/active**
In this case the code would be **braintree_basic**

We hope this is helpful for people! Any suggestions, or bug fixes, are welcome.

You can get the full plugin code here [PeacockCarter Filter Billing Options By Shipping Address](http://github.com/peacockcarter/.....)