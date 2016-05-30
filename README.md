# Filter Payment methods on Shipping Address

Adds the ability to filter core payment methods based on shipping country.
Can still filter on billing country, or both if required.

Using a none core payment method? 
Not a problem, just create a module and add the filter fields to your system.xml file as shown below.

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

Ensure that the group is named the same as the payment method code e.g. authorizenet.

Don't change the field names (i.e. shippingbased and specificshippingcountry), the observer uses these to get your settings from the database.
