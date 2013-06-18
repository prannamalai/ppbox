Credit Card Checkout module
======================
This module is responsible for handling all checkout/payment requests made by providing credit card details. It creates object of PaymentsPro package''s PaymentsPro class to send request to PayPal for credit card checkout.

Creating and Managing Classic API Credentials
---------------------------------------------
When calling this api, you must authenticate each request using a set of API credentials. PayPal associates a set of API credentials with a specific PayPal account, and you can generate credentials for any PayPal Business or Premier account.
This guide describes how to create the credentials you need to make calls to the live PayPal environment.
https://developer.paypal.com/webapps/developer/docs/classic/api/apiCredentials/

To generate api credentials for sanbox environment please refer the below link
https://developer.paypal.com/webapps/developer/docs/classic/lifecycle/ug_sandbox/

How to make calls to this module?
------------------------------------
REST URL: `http://<hostname>/ppbox/rest/checkout` <br>
HTTP Method: POST <br>
Headers: <br>
`method` = `credit_card` <br>
`Content-Type` = `json` or `xml` <br>
`Accept` = `json` or `xml` <br>

Body (Sample for `Content-Type` = `json`): <br>
<pre>
{
  "authentication": {
    "live": "false",
    "api_username": "mer102_1362122176_biz_api1.gmail.com",
    "api_password": "1362122231",
    "api_signature": "AjsbJc8Z7bMatF-pWQPqoJdMdJ4AAf70D6iap-C787dthlUmSAbwoCv-"
  },
  "intent": "sale",
  "payer":{
    "funding_instrument": {
        "credit_card":{
          "number":"4417119669820331",
          "type":"visa",
          "expire_month":11,
          "expire_year":2018,
          "cvv2":874,
          "first_name":"Joe",
          "last_name":"Shopper",
          "billing_address":{
            "line1":"52 N Main ST",
            "city":"Johnstown",
            "zip": "33770",
            "country_code":"US",
            "postal_code":"43210",
            "state":"OH"
          },
          "start_date": "",
          "issue_number": ""
        }
      }
  },
  "items": [
    {
      "item_name": "item 1",
      "item_desc": "item 1 desc",
      "item_price": "1.00",
      "item_tax": "0.20",
      "item_qty": "1"    
    },
    {
      "item_name": "item 2",
      "item_desc": "item 2 desc",
      "item_price": "1.50",
      "item_tax": "0.50",
      "item_qty": "2"    
    }
  ],
  "transaction": {
    "invoice_number": "12345",
     "currency":"USD",
     "shipping":"0.05",
     "description":"This is the payment transaction description."
  }
}
</pre>

Body (Sample for `Content-Type` = `xml`): <br>
<pre>
&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;root&gt;
   &lt;authentication&gt;
      &lt;live&gt;false&lt;/live&gt;
      &lt;api_username&gt;mer102_1362122176_biz_api1.gmail.com&lt;/api_username&gt;
      &lt;api_password&gt;1362122231&lt;/api_password&gt;
      &lt;api_signature&gt;AjsbJc8Z7bMatF-pWQPqoJdMdJ4AAf70D6iap-C787dthlUmSAbwoCv-&lt;/api_signature&gt;
   &lt;/authentication&gt;
   &lt;intent&gt;sale&lt;/intent&gt;
   &lt;payer&gt;
      &lt;funding_instrument&gt;
         &lt;credit_card&gt;
            &lt;billing_address&gt;
               &lt;city&gt;Johnstown&lt;/city&gt;
               &lt;country_code&gt;US&lt;/country_code&gt;
               &lt;line1&gt;52 N Main ST&lt;/line1&gt;
               &lt;postal_code&gt;43210&lt;/postal_code&gt;
               &lt;state&gt;OH&lt;/state&gt;
               &lt;zip&gt;33770&lt;/zip&gt;
            &lt;/billing_address&gt;
            &lt;cvv2&gt;874&lt;/cvv2&gt;
            &lt;expire_month&gt;11&lt;/expire_month&gt;
            &lt;expire_year&gt;2018&lt;/expire_year&gt;
            &lt;first_name&gt;Joe&lt;/first_name&gt;
            &lt;last_name&gt;Shopper&lt;/last_name&gt;
            &lt;number&gt;4417119669820331&lt;/number&gt;
            &lt;type&gt;visa&lt;/type&gt;
         &lt;/credit_card&gt;
      &lt;/funding_instrument&gt;
   &lt;/payer&gt;
   &lt;items&gt;
         &lt;item_name&gt;item 1&lt;/item_name&gt;
         &lt;item_desc&gt;item 1 desc&lt;/item_desc&gt;
         &lt;item_price&gt;1.00&lt;/item_price&gt;
         &lt;item_tax&gt;0.20&lt;/item_tax&gt;
         &lt;item_qty&gt;1&lt;/item_qty&gt;
   &lt;/items&gt;
   &lt;items&gt;
         &lt;item_name&gt;item 2&lt;/item_name&gt;
         &lt;item_desc&gt;item 2 desc&lt;/item_desc&gt;
         &lt;item_price&gt;1.50&lt;/item_price&gt;
         &lt;item_tax&gt;0.50&lt;/item_tax&gt;
         &lt;item_qty&gt;2&lt;/item_qty&gt;
   &lt;/items&gt;
   &lt;transaction&gt;
      &lt;invoice_number&gt;12345&lt;/invoice_number&gt;
      &lt;currency&gt;USD&lt;/currency&gt;
      &lt;shipping&gt;0.05&lt;/shipping&gt;
      &lt;description&gt;This is the payment transaction description.&lt;/description&gt;
   &lt;/transaction&gt;
&lt;/root&gt;
</pre>

Response (Sample for `Accept` = `json`):

<pre>
{
    "status": "success",
    "data": {
        "transaction_id": "1DD78595H3053190V"
    }
}
</pre>

Response (Sample for `Accept` = `xml`):

<pre>
&lt;xml version="1.0"?&gt;
&lt;root&gt;
    &lt;status&gt;success&lt;/status&gt;
    &lt;data&gt;
        &lt;transaction_id&gt;34U94652GC005274A&lt;/transaction_id&gt;
    &lt;/data&gt;
&lt;/root&gt;

</pre>
