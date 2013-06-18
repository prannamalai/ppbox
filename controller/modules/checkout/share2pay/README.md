Share2Pay Checkout module
======================
This module is responsible for handling all checkout/payment requests made for shared payment. A payment url will be created and shared to the intended payers .
You can provide email id/mobileno./facebook id/twitter id of the payers.The payment url will be sent to the email id/mobileno.If facebook id is given ,the url get posted in the payer's facebook page.
If twitter id is given ,he will get a tweet in his twitter account.

This checkout gives a new feature of requesting payment from some one on behalf of another paypal business account holder.
For Eg: Merchant A having paypal account can request payments from customer B,C,D,E on behalf of merchant X who is also having paypal account.
Here merchant X is the person who actually sold the products or provided the service to the customers.

In the below sample request,<br>
creator- Person who raises the request on behalf of the merchant.<br>
payee-email - Email of the merchant,who is the actual party getting paid in this transaction.<br>
payerslist-A comma separated list of the persons who are going to make payments to the merchant.You can mention mobile no.,email id ,facebook id,twitter id of the payer.<br>

Note:
-----
<em>
Share2pay module is dependent on Share2Pay service, which needs to be hosted on another server. We are working on another project which contains the implementation of that service.
Until that project is ready, we are making this module "disabled" by default. We will update the location/source-code of that project shortly.
</em>


How to make calls to this module?
------------------------------------
REST URL: `http://<hostname>/ppbox/rest/checkout` <br>
HTTP Method: POST <br>
Headers: <br>
`method` = `share2pay` <br>
`Content-Type` = `json` or `xml` <br>
`Accept` = `json` or `xml` <br>

Body (Sample for `Content-Type` = `json`): <br>
<pre>
{
  "authentication":{
    "live":"true" 
  },
  "payee":{
    "eventname":"BabyStore-Bill",
    "businessdesc" : "A store for all your baby needs",
    "email":"id@example.com",
    "creator":"id2@example.com"
  },
  "payer":{
    "funding_instrument":{
      "share2pay":{
        "payerslist": "1234567890,consumer1@example.com,consumer2@facebook.com,consumer2@twitter.com"
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
    }],
  "transaction":{
    "minamount":20,
    "maxamount":100
  }
}
</pre>

Response (Sample for `Content-Type` = `json`):

<pre>
{
    "status": "success",
    "data": {
    "handle": "http://www.example.com/pay/id@example.com/amount=5/message=BabyStore-Bill/1231"
    }
}
</pre>

Body (Sample for `Content-Type` = `xml`): <br>
<pre>
&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;
&lt;root&gt;
   &lt;authentication&gt;
      &lt;live&gt;true&lt;/live&gt;
   &lt;/authentication&gt;
    &lt;items&gt;
         &lt;item_desc&gt;item 1 desc&lt;/item_desc&gt;
         &lt;item_name&gt;item 1&lt;/item_name&gt;
         &lt;item_price&gt;1.00&lt;/item_price&gt;
         &lt;item_qty&gt;1&lt;/item_qty&gt;
         &lt;item_tax&gt;0.20&lt;/item_tax&gt;
      &lt;/items&gt;
      &lt;items&gt;
         &lt;item_desc&gt;item 2 desc&lt;/item_desc&gt;
         &lt;item_name&gt;item 2&lt;/item_name&gt;
         &lt;item_price&gt;1.50&lt;/item_price&gt;
         &lt;item_qty&gt;2&lt;/item_qty&gt;
         &lt;item_tax&gt;0.50&lt;/item_tax&gt;
      &lt;/items&gt;
     &lt;payee&gt;
      &lt;businessdesc&gt;A store for all your baby needs&lt;/businessdesc&gt;
      &lt;creator&gt;id2@example.com&lt;/creator&gt;
      &lt;email&gt;id@example.com&lt;/email&gt;
      &lt;eventname&gt;BabyStore-Bill&lt;/eventname&gt;
   &lt;/payee&gt;
   &lt;payer&gt;
      &lt;funding_instrument&gt;
         &lt;share2pay&gt;
            &lt;payerslist&gt;1234567890,consumer1@example.com,consumer2@facebook.com,consumer2@twitter.com&lt;/payerslist&gt;
         &lt;/share2pay&gt;
      &lt;/funding_instrument&gt;
   &lt;/payer&gt;
   &lt;transaction&gt;
      &lt;maxamount&gt;100&lt;/maxamount&gt;
      &lt;minamount&gt;20&lt;/minamount&gt;
   &lt;/payment&gt;
&lt;/root&gt;

</pre>

Response (Sample for `Content-Type` = `xml`):

<pre>
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;root&gt;
    &lt;status&gt;success&lt;/status&gt;
    &lt;data&gt;
        &lt;handle&gt;http://www.example.com/pay/id@example.com/amount=5/message=BabyStore-Bill/1231&gt;
    &lt;/data&gt;
&lt;/root&gt;
</pre>


