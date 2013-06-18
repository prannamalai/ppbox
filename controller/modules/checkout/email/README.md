Email Checkout module
======================
This module is responsible for handling all checkout/payment requests made by providing email address. This will create object of adaptivepay package.

Email checkout supports both simple and parallel payments.Simple payment enables a sender to send a payment to single receiver.Parallel payment enables a sender to send a single payment to multiple receivers. Technically, a parallel payment is a set of multiple payments made in a single Pay request.

For example, your application might be a shopping cart that enables a buyer to pay for items from several merchants with one payment.
Your shopping cart allocates the payment to merchants that actually provided the items. PayPal then deducts money from the
sender’s account and deposits it in the receivers’ accounts.

Approval Payment Flow
----------------------
	1.You send a checkout request to the library in the below mentioned request format.
	2.The library will return back  a paykey,returnurl,trackingid in repsonse
	3.Direct the user to that returnurl.
	4.After the sender authorizes the transfer of funds, PayPal redirects your sender’s browser to
	  the url you specify.
	
Creating and Managing Classic API Credentials
---------------------------------------------
When calling this api, you must authenticate each request using a set of API credentials. PayPal associates a set of API credentials with a specific PayPal account, and you can generate credentials for any PayPal Business or Premier account.
This guide describes how to create the credentials you need to make calls to the live PayPal environment.
https://developer.paypal.com/webapps/developer/docs/classic/api/apiCredentials/

To generate api credentials for sanbox environment please refer the below link
https://developer.paypal.com/webapps/developer/docs/classic/lifecycle/ug_sandbox/

Simple Payment
----------------
REST URL: `http://<hostname>/ppbox/rest/checkout` <br>
HTTP Method: POST <br>
Headers: <br>
`method` = `email` <br>
`Content-Type` = `json` or `xml` <br>
`Accept` = `json` or `xml` <br>

Body (Sample for `Content-Type` = `json`): <br>
<pre>
{
  "authentication": {
    "live": "false",
    "api_username": "mer102_1362122176_biz_api1.gmail.com",
    "api_password": "1362122231",
    "api_signature": "AjsbJc8Z7bMatF-pWQPqoJdMdJ4AAf70D6iap-C787dthlUmSAbwoCv-",
    "api_appid": "APP-80W284485P519543T"
  },
  "url":{
	"return_url":"http://www.example.com",
	"cancel_url":"http://www.example.com"
  },
  "payee":{
    "email": "merchant201_biz@gmail.com"
  },
  "items": [
    {
      "item_name": "item 1",
      "item_desc": "item 1 desc",
      "item_price": "20.00",
      "item_tax": "0.20",
      "item_qty": "1"      
    },
    {
      "item_name": "item 2",
      "item_desc": "item 2 desc",
      "item_price": "20.00",
      "item_tax": "0.20",
      "item_qty": "1"     
    },
    {
      "item_name": "item 3",
      "item_desc": "item 3 desc",
      "item_price": "40.00",
      "item_tax": "0.20",
      "item_qty": "1"    
	}
  ],
  "transaction": {   
     "currency":"USD"
  }
}
</pre>

Response (Sample for `Accept` = `json`):

<pre>
{
    "status": "success",
    "data":{
	"payKey"	  : "AP-85675263RJ500360X",
	"redirectUrl" : "https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay?payKey=AP-85675263RJ500360X",
	"trackingId"  : "3H7oPreJV"	
    }
}
</pre>

Body (Sample for `Content-Type` = `xml`): <br>
<pre>
&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;
&lt;root&gt;
   &lt;authentication&gt;
      &lt;api_appid&gt;APP-80W284485P519543T&lt;/api_appid&gt;
      &lt;api_password&gt;1362122231&lt;/api_password&gt;
      &lt;api_signature&gt;AjsbJc8Z7bMatF-pWQPqoJdMdJ4AAf70D6iap-C787dthlUmSAbwoCv-&lt;/api_signature&gt;
      &lt;api_username&gt;mer102_1362122176_biz_api1.gmail.com&lt;/api_username&gt;
      &lt;live&gt;false&lt;/live&gt;
   &lt;/authentication&gt;
      &lt;items&gt;
         &lt;item_desc&gt;item 1 desc&lt;/item_desc&gt;
         &lt;item_name&gt;item 1&lt;/item_name&gt;
         &lt;item_price&gt;20.00&lt;/item_price&gt;
         &lt;item_qty&gt;1&lt;/item_qty&gt;
         &lt;item_tax&gt;0.20&lt;/item_tax&gt;
      &lt;/items&gt;
      &lt;items&gt;
         &lt;item_desc&gt;item 2 desc&lt;/item_desc&gt;
         &lt;item_name&gt;item 2&lt;/item_name&gt;
         &lt;item_price&gt;20.00&lt;/item_price&gt;
         &lt;item_qty&gt;1&lt;/item_qty&gt;
         &lt;item_tax&gt;0.20&lt;/item_tax&gt;
      &lt;/items&gt;
      &lt;items&gt;
         &lt;item_desc&gt;item 3 desc&lt;/item_desc&gt;
         &lt;item_name&gt;item 3&lt;/item_name&gt;
         &lt;item_price&gt;40.00&lt;/item_price&gt;
         &lt;item_qty&gt;1&lt;/item_qty&gt;
         &lt;item_tax&gt;0.20&lt;/item_tax&gt;
      &lt;/items&gt;
   &lt;payee&gt;
      &lt;email&gt;merchant201_biz@gmail.com&lt;/email&gt;
   &lt;/payee&gt;
   &lt;transaction&gt;
      &lt;currency&gt;USD&lt;/currency&gt;
   &lt;/transaction&gt;
   &lt;url&gt;
      &lt;cancel_url&gt;http://www.example.com&lt;/cancel_url&gt;
      &lt;return_url&gt;http://www.example.com&lt;/return_url&gt;
   &lt;/url&gt;
&lt;/root&gt;
</pre>
Response (Sample for `Accept` = `xml`):

<pre>
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;root&gt;
    &lt;status&gt;success&lt;/status&gt;
    &lt;data&gt;
        &lt;payKey&gt;AP-0B345747PH581835B&lt;/payKey&gt;
        &lt;redirectUrl&gt;https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay?payKey=AP-0B345747PH581835B&lt;/redirectUrl&gt;
        &lt;trackingId&gt;N1fWc8cSc&lt;/trackingId&gt;
    &lt;/data&gt;
&lt;/root&gt;
</pre>

Parallel Payment
----------------
REST URL: `http://<hostname>/ppbox/rest/checkout` <br>
HTTP Method: POST <br>
Headers: <br>
`method` = `email` <br>
`Content-Type` = `json` or `xml` <br>
`Accept` = `json` or `xml` <br>

Body (Sample for `Content-Type` = `json`): <br>
<pre>
{
  "authentication": {
    "live": "false",
    "api_username": "mer102_1362122176_biz_api1.gmail.com",
    "api_password": "1362122231",
    "api_signature": "AjsbJc8Z7bMatF-pWQPqoJdMdJ4AAf70D6iap-C787dthlUmSAbwoCv-",
    "api_appid": "APP-80W284485P519543T"
  },
	"url":{
	"return_url":"http://www.example.com",
	"cancel_url":"http://www.example.com"
  },
  "payee":{
    "email": "mer101_1359454882_biz@gmail.com,merchant201_biz@gmail.com,merchant202_biz@gmail.com"
  },
  "items": [
    {
      "item_name": "item 1",
      "item_desc": "item 1 desc",
      "item_price": "20.00",
      "item_tax": "0.20",
      "item_qty": "1",
      "merchant_email" :"mer101_1359454882_biz@gmail.com"
    },
    {
      "item_name": "item 2",
      "item_desc": "item 2 desc",
      "item_price": "20.00",
      "item_tax": "0.20",
      "item_qty": "1",
	  "merchant_email" :""
    },
    {
      "item_name": "item 3",
      "item_desc": "item 3 desc",
      "item_price": "40.00",
      "item_tax": "0.20",
      "item_qty": "1",
      "merchant_email" :"merchant201_biz@gmail.com"
    },
    {
      "item_name": "item 4",
      "item_desc": "item 4 desc",
      "item_price": "40.00",
      "item_tax": "0.20",
      "item_qty": "1",
      "merchant_email" :"mer101_1359454882_biz@gmail.com"
    }
  ],
  "transaction": {   
     "currency":"USD"
  }
}
</pre>
Response (Sample for `Accept` = `json`):

<pre>
{
    "status": "success",
    "data":{
	"payKey"	  : "AP-99375263RJ500360X",
	"redirectUrl" : "https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay?payKey=AP-99375263RJ500360X",
	"trackingId"  : "3H7oPreJV"	
    }
}
</pre>

Body (Sample for `Content-Type` = `xml`): <br>
<pre>
&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;
&lt;root&gt;
   &lt;authentication&gt;
      &lt;api_appid&gt;APP-80W284485P519543T&lt;/api_appid&gt;
      &lt;api_password&gt;1362122231&lt;/api_password&gt;
      &lt;api_signature&gt;AjsbJc8Z7bMatF-pWQPqoJdMdJ4AAf70D6iap-C787dthlUmSAbwoCv-&lt;/api_signature&gt;
      &lt;api_username&gt;mer102_1362122176_biz_api1.gmail.com&lt;/api_username&gt;
      &lt;live&gt;false&lt;/live&gt;
   &lt;/authentication&gt;
      &lt;items&gt;
         &lt;item_desc&gt;item 1 desc&lt;/item_desc&gt;
         &lt;item_name&gt;item 1&lt;/item_name&gt;
         &lt;item_price&gt;20.00&lt;/item_price&gt;
         &lt;item_qty&gt;1&lt;/item_qty&gt;
         &lt;item_tax&gt;0.20&lt;/item_tax&gt;
         &lt;merchant_email&gt;mer101_1359454882_biz@gmail.com&lt;/merchant_email&gt;
      &lt;/items&gt;
      &lt;items&gt;
         &lt;item_desc&gt;item 2 desc&lt;/item_desc&gt;
         &lt;item_name&gt;item 2&lt;/item_name&gt;
         &lt;item_price&gt;20.00&lt;/item_price&gt;
         &lt;item_qty&gt;1&lt;/item_qty&gt;
         &lt;item_tax&gt;0.20&lt;/item_tax&gt;
         &lt;merchant_email&gt;merchant201_biz@gmail.com&lt;/merchant_email&gt;
      &lt;/items&gt;
      &lt;items&gt;
         &lt;item_desc&gt;item 3 desc&lt;/item_desc&gt;
         &lt;item_name&gt;item 3&lt;/item_name&gt;
         &lt;item_price&gt;40.00&lt;/item_price&gt;
         &lt;item_qty&gt;1&lt;/item_qty&gt;
         &lt;item_tax&gt;0.20&lt;/item_tax&gt;
         &lt;merchant_email&gt;merchant202_biz@gmail.com&lt;/merchant_email&gt;
      &lt;/items&gt;
      &lt;items&gt;
         &lt;item_desc&gt;item 4 desc&lt;/item_desc&gt;
         &lt;item_name&gt;item 4&lt;/item_name&gt;
         &lt;item_price&gt;40.00&lt;/item_price&gt;
         &lt;item_qty&gt;1&lt;/item_qty&gt;
         &lt;item_tax&gt;0.20&lt;/item_tax&gt;
         &lt;merchant_email&gt;mer101_1359454882_biz@gmail.com&lt;/merchant_email&gt;
      &lt;/items&gt;
   &lt;payee&gt;
      &lt;email&gt;mer101_1359454882_biz@gmail.com,merchant201_biz@gmail.com,merchant202_biz@gmail.com&lt;/email&gt;
   &lt;/payee&gt;
   &lt;transaction&gt;
      &lt;currency&gt;USD&lt;/currency&gt;
   &lt;/transaction&gt;
   &lt;url&gt;
      &lt;cancel_url&gt;http://www.example.com&lt;/cancel_url&gt;
      &lt;return_url&gt;http://www.example.com&lt;/return_url&gt;
   &lt;/url&gt;
&lt;/root&gt;

</pre>

Response (Sample for `Accept` = `xml`):

<pre>
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;root&gt;
    &lt;status&gt;success&lt;/status&gt;
    &lt;data&gt;
        &lt;payKey&gt;AP-0B345747PH581835B&lt;/payKey&gt;
        &lt;redirectUrl&gt;https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay?payKey=AP-0B345747PH581835B&lt;/redirectUrl&gt;
        &lt;trackingId&gt;N1fWc8cSc&lt;/trackingId&gt;
    &lt;/data&gt;
&lt;/root&gt;
</pre>
