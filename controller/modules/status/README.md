Status module
======================
This module is responsible for handling all status requests. The status module is used to confirm to confirm whether a payment made by 
consumer using PayPal is success or failure.

Class Status
--------------
This Status class represents the main status controller. 
For example, <br><br>
`public function get_email($req)` <br><br>
is a HTTP get method handler for getting the payment's status.

Getting Payment's status
---------------------------
Use an HTTP GET request to the status resource to check on what is happening with your payment in your account history.

<b>Creating and Managing Classic API Credentials :</b>
When calling this api, you must authenticate each request using a set of API credentials. PayPal associates a set of API credentials with a specific PayPal account, and you can generate credentials for any PayPal Business or Premier account.
This guide describes how to create the credentials you need to make calls.
* Live account - https://developer.paypal.com/webapps/developer/docs/classic/api/apiCredentials/
* Sanbox account - https://developer.paypal.com/webapps/developer/docs/classic/lifecycle/ug_sandbox/

###Request (Sample for `Accept` = `json`):<br>###
REST URL: `http://<hostname>/ppbox/rest/status` <br>
HTTP Method: GET <br>
Headers: <br>
`method` = `email` <br>
`Accept` = `json` <br>
URL params: <br>
<pre>
`api_username` = `mer102_1362122176_biz_api1.gmail.com` 
`api_password` = `1362122231` 
`api_signature` = `AjsbJc8Z7bMatF-pWQPqoJdMdJ4AAf70D6iap-C787dthlUmSAbwoCv-` 
`live` = `false` 
`api_appid` = `APP-80W284485P519543T`
`payKey` = `AP-57H06625C1355893W` 
</pre>


###Response (Sample for `Accept` = `json`):<br>###
<pre>
{
	"status":"success",
	"data":{	
		"responseEnvelope.timestamp":"2013-05-06T04:22:01.833-07:00",
		"responseEnvelope.ack":"Success",
		"responseEnvelope.correlationId":"eaaee7ff32e69","responseEnvelope.build":"5710487",
		"cancelUrl":"http://www.example.com",
		"currencyCode":"USD",	
		"paymentInfoList.paymentInfo(0).receiver.amount":"60.40",
		"paymentInfoList.paymentInfo(0).receiver.email":"mer101_1359454882_biz@gmail.com",
		"paymentInfoList.paymentInfo(0).receiver.primary":"false",
		"paymentInfoList.paymentInfo(0).receiver.paymentType":"SERVICE",
		"paymentInfoList.paymentInfo(0).receiver.accountId":"4PJWZVWJKG6AW",
		"paymentInfoList.paymentInfo(0).pendingRefund":"false",
		"paymentInfoList.paymentInfo(1).receiver.amount":"20.20",
		"paymentInfoList.paymentInfo(1).receiver.email":"merchant201_biz@gmail.com",
		"paymentInfoList.paymentInfo(1).receiver.primary":"false",
		"paymentInfoList.paymentInfo(1).receiver.paymentType":"SERVICE",
		"paymentInfoList.paymentInfo(1).receiver.accountId":"DTHA6YTZ6SJ6C",
		"paymentInfoList.paymentInfo(1).pendingRefund":"false",
		"paymentInfoList.paymentInfo(2).receiver.amount":"40.20",
		"paymentInfoList.paymentInfo(2).receiver.email":"merchant202_biz@gmail.com",
		"paymentInfoList.paymentInfo(2).receiver.primary":"false",
		"paymentInfoList.paymentInfo(2).receiver.paymentType":"SERVICE",
		"paymentInfoList.paymentInfo(2).receiver.accountId":"6L3WNCSZKX668",
		"paymentInfoList.paymentInfo(2).pendingRefund":"false",
		"returnUrl":"http://localhost/shorturl/ppevents/pay/paysuccess.php",	
		"status":"CREATED",
		"trackingId":"IxFgw4cTh",
		"payKey":"AP-57H06625C1355893W",
		"actionType":"PAY",
		"feesPayer":"EACHRECEIVER",
		"reverseAllParallelPaymentsOnError":"false",
		"sender.accountId":"0",
		"sender.useCredentials":"false"
		}
}
</pre>

###Request (Sample for `Accept` = `xml`):<br>###
REST URL: `http://<hostname>/ppbox/rest/status` <br>
HTTP Method: GET <br>
Headers: <br>
`method` = `email` <br>
`Accept` = `xml` <br>

URL params: <br>
<pre>
`api_username` = `mer102_1362122176_biz_api1.gmail.com` 
`api_password` = `1362122231` 
`api_signature` = `AjsbJc8Z7bMatF-pWQPqoJdMdJ4AAf70D6iap-C787dthlUmSAbwoCv-` 
`live` = `false` 
`api_appid` = `APP-80W284485P519543T`
`payKey` = `AP-57H06625C1355893W` 
</pre>


###Response (Sample for `Accept` = `xml`):<br>###
<pre>
&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot; ?&gt;
&lt;root&gt;
	&lt;status&gt;success&lt;/status&gt;
	&lt;data&gt;
		&lt;responseEnvelope.timestamp&gt;2013-05-06T04:22:01.833-07:00&lt;/responseEnvelope.timestamp&gt;
		&lt;responseEnvelope.ack&gt;Success&lt;/responseEnvelope.ack&gt;
		&lt;responseEnvelope.correlationId&gt;eaaee7ff32e69&lt;/responseEnvelope.correlationId&gt;
		&lt;responseEnvelope.build&gt;5710487&lt;/responseEnvelope.build&gt;
		&lt;cancelUrl&gt;http://www.example.com&lt;/cancelUrl&gt;
		&lt;currencyCode&gt;USD&lt;/currencyCode&gt;
		&lt;paymentInfoList.paymentInfo(0).receiver.amount&gt;60.40&lt;/paymentInfoList.paymentInfo(0).receiver.amount&gt;
		&lt;paymentInfoList.paymentInfo(0).receiver.email&gt;mer101_1359454882_biz@gmail.com&lt;/paymentInfoList.paymentInfo(0).receiver.email&gt;
		&lt;paymentInfoList.paymentInfo(0).receiver.primary&gt;false&lt;/paymentInfoList.paymentInfo(0).receiver.primary&gt;
		&lt;paymentInfoList.paymentInfo(0).receiver.paymentType&gt;SERVICE&lt;/paymentInfoList.paymentInfo(0).receiver.paymentType&gt;
		&lt;paymentInfoList.paymentInfo(0).receiver.accountId&gt;4PJWZVWJKG6AW&lt;/paymentInfoList.paymentInfo(0).receiver.accountId&gt;
		&lt;paymentInfoList.paymentInfo(0).pendingRefund&gt;false&lt;/paymentInfoList.paymentInfo(0).pendingRefund&gt;
		&lt;paymentInfoList.paymentInfo(1).receiver.amount&gt;20.20&lt;/paymentInfoList.paymentInfo(1).receiver.amount&gt;
		&lt;paymentInfoList.paymentInfo(1).receiver.email&gt;merchant201_biz@gmail.com&lt;/paymentInfoList.paymentInfo(1).receiver.email&gt;
		&lt;paymentInfoList.paymentInfo(1).receiver.primary&gt;false&lt;/paymentInfoList.paymentInfo(1).receiver.primary&gt;
		&lt;paymentInfoList.paymentInfo(1).receiver.paymentType&gt;SERVICE&lt;/paymentInfoList.paymentInfo(1).receiver.paymentType&gt;
		&lt;paymentInfoList.paymentInfo(1).receiver.accountId&gt;DTHA6YTZ6SJ6C&lt;/paymentInfoList.paymentInfo(1).receiver.accountId&gt;
		&lt;paymentInfoList.paymentInfo(1).pendingRefund&gt;false&lt;/paymentInfoList.paymentInfo(1).pendingRefund&gt;
		&lt;paymentInfoList.paymentInfo(2).receiver.amount&gt;40.20&lt;/paymentInfoList.paymentInfo(2).receiver.amount&gt;
		&lt;paymentInfoList.paymentInfo(2).receiver.email&gt;merchant202_biz@gmail.com&lt;/paymentInfoList.paymentInfo(2).receiver.email&gt;
		&lt;paymentInfoList.paymentInfo(2).receiver.primary&gt;false&lt;/paymentInfoList.paymentInfo(2).receiver.primary&gt;
		&lt;paymentInfoList.paymentInfo(2).receiver.paymentType&gt;SERVICE&lt;/paymentInfoList.paymentInfo(2).receiver.paymentType&gt;
		&lt;paymentInfoList.paymentInfo(2).receiver.accountId&gt;6L3WNCSZKX668&lt;/paymentInfoList.paymentInfo(2).receiver.accountId&gt;
		&lt;paymentInfoList.paymentInfo(2).pendingRefund&gt;false&lt;/paymentInfoList.paymentInfo(2).pendingRefund&gt;
		&lt;returnUrl&gt;http://localhost/shorturl/ppevents/pay/paysuccess.php&lt;/returnUrl&gt;
		&lt;status&gt;CREATED&lt;/status&gt;
		&lt;trackingId&gt;IxFgw4cTh&lt;/trackingId&gt;
		&lt;payKey&gt;AP-57H06625C1355893W&lt;/payKey&gt;
		&lt;actionType&gt;PAY&lt;/actionType&gt;
		&lt;feesPayer&gt;EACHRECEIVER&lt;/feesPayer&gt;
		&lt;reverseAllParallelPaymentsOnError&gt;false&lt;/reverseAllParallelPaymentsOnError&gt;
		&lt;sender.accountId&gt;0&lt;/sender.accountId&gt;
		&lt;sender.useCredentials&gt;false&lt;/sender.useCredentials&gt;
	&lt;/data&gt;
&lt;/root&gt;	
</pre>
