<?php
/**
 * Copyright (c) 2013 Sridevi Marimuthu.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

//require_once('api/paymentspro/includes/config.php');
class PayPalPlatform 
{
	private $apiEndPoint;
	private $apiUserName;
	private $apiPassword;
	private $apiSignature;
	private $proxyHost;
	private $proxyPort;
	private $environment;
	private $appId;	
	private $trackingId;
	
	public function setAppId($appId)
	{
		$this->appId=$appId;
	}
	
	public function setAPIEndPoint($apiEndPoint)
	{
		$this->apiEndPoint=$apiEndPoint;
	}
	
	public function setAPIUserName($apiUserName)
	{
		$this->apiUserName=$apiUserName;
	}
	
	public function setAPIPassword($apiPassword)
	{
		$this->apiPassword=$apiPassword;
	}
	
	public function setAPISignature($apiSignature)
	{
		$this->apiSignature=$apiSignature;
	}
	
	public function setProxyHost($proxyHost)
	{
		$this->proxyHost=$proxyHost;
	}
	
	public function setProxyPort($proxyPort)
	{
		$this->proxyPort=$proxyPort;
	}
	
	public function setEnvironment($environment)
	{
		$this->environment=$environment;
	}
	
	public function setTrackingId($trackingId)
	{
		$this->trackingId=$trackingId;
	}
	
	public function getTrackingId()
	{
		return $this->trackingId();
	}

	public function generateCharacter() {
		$possible = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
		return $char;
	}

	public function generateTrackingID () {
		$GUID = $this->generateCharacter().$this->generateCharacter().$this->generateCharacter().$this->generateCharacter().$this->generateCharacter();
		$GUID .= $this->generateCharacter().$this->generateCharacter().$this->generateCharacter().$this->generateCharacter();
		return $GUID;
	}

	/*
	'-------------------------------------------------------------------------------------------------------------------------------------------
	' Purpose: 	Prepares the parameters for the Refund API Call.
	'			The API credentials used in a Pay call can make the Refund call
	'			against a payKey, or a tracking id, or to specific receivers of a payKey or a tracking id
	'			that resulted from the Pay call
	'
	'			A receiver itself with its own API credentials can make a Refund call against the transactionId corresponding to their transaction.
	'			The API credentials used in a Pay call cannot use transactionId to issue a refund
	'			for a transaction for which they themselves were not the receiver
	'
	'			If you do specify specific receivers, keep in mind that you must provide the amounts as well
	'			If you specify a transactionId, then only the receiver of that transactionId is affected therefore
	'			the receiverEmailArray and receiverAmountArray should have 1 entry each if you do want to give a partial refund
	' Inputs:
	'
	' Conditionally Required:
	'		One of the following:  payKey or trackingId or trasactionId or
	'                              (payKey and receiverEmailArray and receiverAmountArray) or
	'                              (trackingId and receiverEmailArray and receiverAmountArray) or
	'                              (transactionId and receiverEmailArray and receiverAmountArray)
	' Returns: 
	'		The NVP Collection object of the Refund call response.
	'--------------------------------------------------------------------------------------------------------------------------------------------	
	*/
	public function callRefund( $payKey, $transactionId, $trackingId, $receiverEmailArray, $receiverAmountArray )
	{
		/* Gather the information to make the Refund call.
			The variable nvpstr holds the name value pairs
		*/
		
		$nvpstr = "";
		
		// conditionally required fields
		if ("" != $payKey)
		{
			$nvpstr = "payKey=" . urlencode($payKey);
			if (0 != count($receiverEmailArray))
			{
				reset($receiverEmailArray);
				while (list($key, $value) = each($receiverEmailArray))
				{
					if ("" != $value)
					{
						$nvpstr .= "&receiverList.receiver(" . $key . ").email=" . urlencode($value);
					}
				}
			}
			if (0 != count($receiverAmountArray))
			{
				reset($receiverAmountArray);
				while (list($key, $value) = each($receiverAmountArray))
				{
					if ("" != $value)
					{
						$nvpstr .= "&receiverList.receiver(" . $key . ").amount=" . urlencode($value);
					}
				}
			}
		}
		elseif ("" != $trackingId)
		{
			$nvpstr = "trackingId=" . urlencode($trackingId);
			if (0 != count($receiverEmailArray))
			{
				reset($receiverEmailArray);
				while (list($key, $value) = each($receiverEmailArray))
				{
					if ("" != $value)
					{
						$nvpstr .= "&receiverList.receiver(" . $key . ").email=" . urlencode($value);
					}
				}
			}
			if (0 != count($receiverAmountArray))
			{
				reset($receiverAmountArray);
				while (list($key, $value) = each($receiverAmountArray))
				{
					if ("" != $value)
					{
						$nvpstr .= "&receiverList.receiver(" . $key . ").amount=" . urlencode($value);
					}
				}
			}
		}
		elseif ("" != $transactionId)
		{
			$nvpstr = "transactionId=" . urlencode($transactionId);
			// the caller should only have 1 entry in the email and amount arrays
			if (0 != count($receiverEmailArray))
			{
				reset($receiverEmailArray);
				while (list($key, $value) = each($receiverEmailArray))
				{
					if ("" != $value)
					{
						$nvpstr .= "&receiverList.receiver(" . $key . ").email=" . urlencode($value);
					}
				}
			}
			if (0 != count($receiverAmountArray))
			{
				reset($receiverAmountArray);
				while (list($key, $value) = each($receiverAmountArray))
				{
					if ("" != $value)
					{
						$nvpstr .= "&receiverList.receiver(" . $key . ").amount=" . urlencode($value);
					}
				}
			}
		}

		/* Make the Refund call to PayPal */
		$resArray = hashCall("Refund", $nvpstr);

		/* Return the response array */
		return $resArray;
	}
	
	/*
	'-------------------------------------------------------------------------------------------------------------------------------------------
	' Purpose: 	Prepares the parameters for the PaymentDetails API Call.
	'			The PaymentDetails call can be made with either
	'			a payKey, a trackingId, or a transactionId of a previously successful Pay call.
	' Inputs:
	'
	' Conditionally Required:
	'		One of the following:  payKey or transactionId or trackingId
	' Returns: 
	'		The NVP Collection object of the PaymentDetails call response.
	'--------------------------------------------------------------------------------------------------------------------------------------------	
	*/
	public function callPaymentDetails( $payKey, $transactionId, $trackingId )
	{
		/* Gather the information to make the PaymentDetails call.
			The variable nvpstr holds the name value pairs
		*/
		
		$nvpstr = "";
		
		// conditionally required fields
		if ("" != $payKey)
		{
			$nvpstr = "payKey=" . urlencode($payKey);
		}
		elseif ("" != $transactionId)
		{
			$nvpstr = "transactionId=" . urlencode($transactionId);
		}
		elseif ("" != $trackingId)
		{
			$nvpstr = "trackingId=" . urlencode($trackingId);
		}

		/* Make the PaymentDetails call to PayPal */
		$resArray = $this->hashCall("PaymentDetails", $nvpstr);
		
		/* Return the response array */
		return $resArray;
	}

	/*
	'-------------------------------------------------------------------------------------------------------------------------------------------
	' Purpose: 	Prepares the parameters for the Pay API Call.
	' Inputs:
	'
	' Required:
	'
	' Optional:
	'
	'		
	' Returns: 
	'		The NVP Collection object of the Pay call response.
	'--------------------------------------------------------------------------------------------------------------------------------------------	
	*/
	public function callPay( $actionType, $cancelUrl, $returnUrl, $currencyCode, $receiverEmailArray, $receiverAmountArray,
						$receiverPrimaryArray, $receiverInvoiceIdArray, $feesPayer, $ipnNotificationUrl,
						$memo, $pin, $preapprovalKey, $reverseAllParallelPaymentsOnError, $senderEmail, $trackingId )
	{
		/* Gather the information to make the Pay call.
			The variable nvpstr holds the name value pairs
		*/
			
		// required fields
		$nvpstr = "actionType=" . urlencode($actionType) . "&currencyCode=" . urlencode($currencyCode);
		$nvpstr .= "&returnUrl=" . urlencode($returnUrl) . "&cancelUrl=" . urlencode($cancelUrl);

		if (0 != count($receiverAmountArray))
		{
			reset($receiverAmountArray);
			while (list($key, $value) = each($receiverAmountArray))
			{
				if ("" != $value)
				{					
					$nvpstr .= "&receiverList.receiver(" . $key . ").amount=" . urlencode($value);
				}
			}
		}
	
		if (0 != count($receiverEmailArray))
		{
			reset($receiverEmailArray);
			while (list($key, $value) = each($receiverEmailArray))
			{
				if ("" != $value)
				{
					$nvpstr .= "&receiverList.receiver(" . $key . ").email=" . urlencode($value);
				}
			}
		}
		
		if (0 != count($receiverPrimaryArray))
		{	
			reset($receiverPrimaryArray);
			while (list($key, $value) = each($receiverPrimaryArray))
			{
				if ("" != $value)
				{
					$nvpstr = $nvpstr . "&receiverList.receiver(" . $key . ").primary=" . urlencode($value);
				}
			}
		}
		


		if (0 != count($receiverInvoiceIdArray))
		{
			reset($receiverInvoiceIdArray);
			while (list($key, $value) = each($receiverInvoiceIdArray))
			{
				if ("" != $value)
				{
					$nvpstr = $nvpstr . "&receiverList.receiver(" . $key . ").invoiceId=" . urlencode($value);
				}
			}
		}
	
		// optional fields
		if ("" != $feesPayer)
		{
			$nvpstr .= "&feesPayer=" . urlencode($feesPayer);
		}

		if ("" != $ipnNotificationUrl)
		{
			$nvpstr .= "&ipnNotificationUrl=" . urlencode($ipnNotificationUrl);
		}

		if ("" != $memo)
		{
			$nvpstr .= "&memo=" . urlencode($memo);
		}

		if ("" != $pin)
		{
			$nvpstr .= "&pin=" . urlencode($pin);
		}

		if ("" != $preapprovalKey)
		{
			$nvpstr .= "&preapprovalKey=" . urlencode($preapprovalKey);
		}

		if ("" != $reverseAllParallelPaymentsOnError)
		{
			$nvpstr .= "&reverseAllParallelPaymentsOnError=" . urlencode($reverseAllParallelPaymentsOnError);
		}

		if ("" != $senderEmail)
		{
			$nvpstr .= "&senderEmail=" . urlencode($senderEmail);
		}

		if ("" != $trackingId)
		{
			$nvpstr .= "&trackingId=" . urlencode($trackingId);
		}	

		/* Make the Pay call to PayPal */
		$resArray = $this->hashCall("Pay", $nvpstr);

		/* Return the response array */
		return $resArray;
	}

	/*
	'-------------------------------------------------------------------------------------------------------------------------------------------
	' Purpose: 	Prepares the parameters for the PreapprovalDetails API Call.
	' Inputs:
	'
	' Required:
	'		preapprovalKey:		A preapproval key that identifies the agreement resulting from a previously successful Preapproval call.
	' Returns: 
	'		The NVP Collection object of the PreapprovalDetails call response.
	'--------------------------------------------------------------------------------------------------------------------------------------------	
	*/
	public function callPreapprovalDetails( $preapprovalKey )
	{
		/* Gather the information to make the PreapprovalDetails call.
			The variable nvpstr holds the name value pairs
		*/
		
		// required fields
		$nvpstr = "preapprovalKey=" . urlencode($preapprovalKey);

		/* Make the PreapprovalDetails call to PayPal */
		$resArray = hashCall("PreapprovalDetails", $nvpstr);

		/* Return the response array */
		return $resArray;
	}
	
	/*
	'-------------------------------------------------------------------------------------------------------------------------------------------
	' Purpose: 	Prepares the parameters for the Preapproval API Call.
	' Inputs:
	'
	' Required:
	'
	' Optional:
	'
	'		
	' Returns: 
	'		The NVP Collection object of the Preapproval call response.
	'--------------------------------------------------------------------------------------------------------------------------------------------	
	*/
	public function callPreapproval( $returnUrl, $cancelUrl, $currencyCode, $startingDate, $endingDate, $maxTotalAmountOfAllPayments,
								$senderEmail, $maxNumberOfPayments, $paymentPeriod, $dateOfMonth, $dayOfWeek,
								$maxAmountPerPayment, $maxNumberOfPaymentsPerPeriod, $pinType )
	{
		/* Gather the information to make the Preapproval call.
			The variable nvpstr holds the name value pairs
		*/
		
		// required fields
		$nvpstr = "returnUrl=" . urlencode($returnUrl) . "&cancelUrl=" . urlencode($cancelUrl);
		$nvpstr .= "&currencyCode=" . urlencode($currencyCode) . "&startingDate=" . urlencode($startingDate);
		$nvpstr .= "&endingDate=" . urlencode($endingDate);
		$nvpstr .= "&maxTotalAmountOfAllPayments=" . urlencode($maxTotalAmountOfAllPayments);
		
		// optional fields
		if ("" != $senderEmail)
		{
			$nvpstr .= "&senderEmail=" . urlencode($senderEmail);
		}

		if ("" != $maxNumberOfPayments)
		{
			$nvpstr .= "&maxNumberOfPayments=" . urlencode($maxNumberOfPayments);
		}
		
		if ("" != $paymentPeriod)
		{
			$nvpstr .= "&paymentPeriod=" . urlencode($paymentPeriod);
		}

		if ("" != $dateOfMonth)
		{
			$nvpstr .= "&dateOfMonth=" . urlencode($dateOfMonth);
		}

		if ("" != $dayOfWeek)
		{
			$nvpstr .= "&dayOfWeek=" . urlencode($dayOfWeek);
		}

		if ("" != $maxAmountPerPayment)
		{
			$nvpstr .= "&maxAmountPerPayment=" . urlencode($maxAmountPerPayment);
		}

		if ("" != $maxNumberOfPaymentsPerPeriod)
		{
			$nvpstr .= "&maxNumberOfPaymentsPerPeriod=" . urlencode($maxNumberOfPaymentsPerPeriod);
		}

		if ("" != $pinType)
		{
			$nvpstr .= "&pinType=" . urlencode($pinType);
		}

		/* Make the Preapproval call to PayPal */
		$resArray = hashCall("Preapproval", $nvpstr);
		/* Return the response array */
		return $resArray;
	}

	/**
	  '-------------------------------------------------------------------------------------------------------------------------------------------
	  * hashCall: Function to perform the API call to PayPal using API signature
	  * @methodName is name of API method.
	  * @nvpStr is nvp string.
	  * returns an associative array containing the response from the server.
	  '-------------------------------------------------------------------------------------------------------------------------------------------
	*/
	public function hashCall($methodName, $nvpStr)
	{
		//declaring of global variables
		/*global $API_Endpoint, $API_UserName, $API_Password, $API_Signature, $API_AppID;
		global $USE_PROXY, $PROXY_HOST, $PROXY_PORT;*/

		$this->apiEndPoint.= "/" . $methodName;
		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->apiEndPoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		
		
		// Set the HTTP Headers
		curl_setopt($ch, CURLOPT_HTTPHEADER,  array(
		'X-PAYPAL-REQUEST-DATA-FORMAT: NV',
		'X-PAYPAL-RESPONSE-DATA-FORMAT: NV',
		'X-PAYPAL-SECURITY-USERID: ' . $this->apiUserName,
		'X-PAYPAL-SECURITY-PASSWORD: ' .$this->apiPassword,
		'X-PAYPAL-SECURITY-SIGNATURE: ' . $this->apiSignature,
		'X-PAYPAL-SERVICE-VERSION: 1.3.0',
		'X-PAYPAL-APPLICATION-ID: ' . $this->appId
		));
	
	
	    //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
		//Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
		/*if($USE_PROXY)
			curl_setopt ($ch, CURLOPT_PROXY, $this->proxyHost. ":" . $this->ProxyPort); 
			var_dump("after proxy");*/

		// RequestEnvelope fields
		$detailLevel	= urlencode("ReturnAll");	// See DetailLevelCode in the WSDL for valid enumerations
		$errorLanguage	= urlencode("en_US");		// This should be the standard RFC 3066 language identification tag, e.g., en_US

		// NVPRequest for submitting to server
		$nvpreq = "requestEnvelope.errorLanguage=$errorLanguage&requestEnvelope.detailLevel=$detailLevel";
		$nvpreq .= "&$nvpStr";

		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);	

		//getting response from server
		$response = curl_exec($ch);
		
		//converting NVPResponse to an Associative Array
		$nvpResArray=$this->deformatNVP($response);
		$nvpReqArray=$this->deformatNVP($nvpreq);
	
		//$_SESSION['nvpReqArray']=$nvpReqArray;

		if (curl_errno($ch)) 
		{
			// moving to display page to display curl errors
			  /*$_SESSION['curl_error_no']=curl_errno($ch) ;
			  $_SESSION['curl_error_msg']=curl_error($ch);*/

			  //Execute the Error handling module to display errors. 
		} 
		else 
		{
			 //closing the curl
		  	curl_close($ch);
		}	

		return $nvpResArray;
	}

	/*'----------------------------------------------------------------------------------
	 Purpose: Redirects to PayPal.com site.
	 Inputs:  $cmd is the querystring
	 Returns: 
	----------------------------------------------------------------------------------
	*/
	public function RedirectToPayPal ( $cmd )
	{
		// Redirect to paypal.com here
		global $Env;

		$payPalURL = "";
		
		if ($Env == "sandbox") 
		{
			$payPalURL = "https://www.sandbox.paypal.com/webscr?" . $cmd;
		}
		else
		{
			$payPalURL = "https://www.paypal.com/webscr?" . $cmd;
		}

		header("Location: ".$payPalURL);
		exit;
	}

	
	/*'----------------------------------------------------------------------------------
	 * This function will take NVPString and convert it to an Associative Array and it will decode the response.
	  * It is usefull to search for a particular key and displaying arrays.
	  * @nvpstr is NVPString.
	  * @nvpArray is Associative Array.
	   ----------------------------------------------------------------------------------
	  */
	public function deformatNVP($nvpstr)
	{
		$intial=0;
	 	$nvpArray = array();

		while(strlen($nvpstr))
		{
			//postion of Key
			$keypos= strpos($nvpstr,'=');
			//position of value
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);

			/*getting the Key and Value values and storing in a Associative Array*/
			$keyval=substr($nvpstr,$intial,$keypos);
			$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
			//decoding the respose
			$nvpArray[urldecode($keyval)] =urldecode( $valval);
			$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
	     }
		return $nvpArray;
	}
}

?>

