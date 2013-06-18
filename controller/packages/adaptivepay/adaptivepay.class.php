<?php
/**
 * Copyright (c) 2013 Sridevi Marimuthu
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

chdir(__DIR__);
include_once('../paypalcheckout.interface.php');
require_once ("paypalplatform.class.php");

/**
 * Adaptive class - responsible for functionality related to email based payment
 */
class AdaptivePay implements IPayPalCheckout {

	private $endPointURL;
	private $apiEndPoint;
	private $logger;
	private $paypalPlatform;

	/**
	 * Initializer function
	 * 
	 * @throws Exception
	 */
	private function init($target) {
		//Reading endpoints from ini file
		$ini_array = parse_ini_file('adaptivepay.ini', true);

		if($target == 'live') {
			if(isset($ini_array['endpoints']['live']) && isset($ini_array['apiendpoints']['live'])) {

				$this->endPointURL = $ini_array['endpoints']['live'];
				$this->apiEndPoint = $ini_array['apiendpoints']['live'];			

			} else {
				throw new Exception("Live endpoints undefined");
			}
		} elseif($target == 'sandbox') {
			if(isset($ini_array['endpoints']['sandbox']) && isset($ini_array['apiendpoints']['sandbox'])) {
			
				$this->endPointURL = $ini_array['endpoints']['sandbox'];
				$this->apiEndPoint = $ini_array['apiendpoints']['sandbox'];			

			} else {
				throw new Exception("Sandbox endpoints undefined");
			}
		}
	}

	/**
	 * IPayPalCheckout interface mandate
	 * 
	 * Do the checkout using input request
	 *
	 * @access	public
	 * @param	BaseRequest	
	 * @return	BaseResponse	
	 */
	public function doCheckout($req) {
		//setting the logger
		$this->logger = Logger::getLogger("AdaptivePay");
	
		$body = $req->asArray();
		
		//Checking if input data is in proper format
		if(null == $body || !isset($body['authentication'])) {
			$this->logger->error("The input data is not in the specified format ");			
			return Util::generateErrResponse('error',ERR_BAD_INPUT);			
		}
		//Checking whether this is live or sandbox call
		$this->logger->info("Checking whether this is live or sandbox call");
		try {
			if($body["authentication"]["live"] == TRUE) {
				$this->logger->info("This call is made in live environment");
				$this->init('live');
			} else {
				$this->logger->info("This call is made in sandbox environment");
				$this->init('sandbox');
			}
		} catch(Exception $e) {
			$this->logger->error("Config file exception : ".$e->getMessage());		
			return Util::generateErrResponse('error',ERR_INTERNAL_EXCEPTION);		
		}
		$this->logger->info("Validating the input data");
		//validating the input data
		if($this->validateInputData($req) == false) {
			$this->logger->error("Field validation failed");
			return Util::generateErrResponse('error',ERR_FIELD_VALIDATION_FAILED);		
		}	
		$this->logger->info("Extracting the required fields from the data");
		//Extracting the data fields from input data
		$inputData = array();
		//Authentication information
		$apiUserName = isset($body["authentication"]["api_username"]) ? $body["authentication"]["api_username"] : "";
		$apiPassword = isset($body["authentication"]["api_password"]) ? $body["authentication"]["api_password"] : "";	
		$apiSignature = isset($body["authentication"]["api_signature"]) ? $body["authentication"]["api_signature"] : "";
		$apiproxyHost = isset($body["authentication"]["api_proxyhost"]) ? $body["authentication"]["api_proxyhost"] : "";
		$apiproxyPort = isset($body["authentication"]["api_proxyport"]) ? $body["authentication"]["api_proxyport"] : "";
		$appID = isset($body["authentication"]["api_appid"]) ? $body["authentication"]["api_appid"] : "";

		//Url details
		$returnUrl = isset($body["url"]["return_url"]) ? urldecode($body["url"]["return_url"]) : "";
		$cancelUrl = isset($body["url"]["cancel_url"]) ? urldecode($body["url"]["cancel_url"]) : "";

		// Payee information
		$tempArray= isset( $body["payee"]["email"]) ? $body["payee"]["email"] : "";
		$merchantEmails = explode(",",$tempArray);

		//Payer Information
		$inputData["senderEmail"] = isset($body["payer"]["senderEmail"] ) ? $body["payer"]["senderEmail"] : "";

		// Transaction information
		$primaryIndicators = isset( $body["transaction"]["primaryIndicators"]) ? $body["payee"]["primaryIndicators"] : NULL;		
		$currencyCode= isset( $body["transaction"]["currency"] ) ? $body["transaction"]["currency"] : "";
		$invoiceNumbers = isset( $body["transaction"]["invoices"] ) ? $body["transaction"]["invoices"] : NULL;		
		$inputData["feesPayer"] = isset( $body["transaction"]["feesPayer"] ) ? $body["transaction"]["feesPayer"] : "";
		$inputData["memo"] = isset( $body["transaction"]["memo"] ) ? $body["transaction"]["memo"] : "";
		$inputData["ipnNotificationUrl"] = isset( $body["transaction"]["ipnNotificationUrl"] ) ? $body["transaction"]["ipnNotificationUrl"] : "";
		$inputData["pin"] = isset( $body["transaction"]["pin"] ) ? $body["transaction"]["pin"] : "";
		$inputData["pin"] = isset( $body["transaction"]["pin"] ) ? $body["transaction"]["pin"] : "";
		$inputData["preApprovalKey"] = isset( $body["transaction"]["preApprovalKey"] ) ? $body["transaction"]["preApprovalKey"] : "";
		$inputData["reverseAllParallelPaymentsOnError"] = isset( $body["transaction"]["reverseAllParallelPaymentsOnError"] ) ? $body["transaction"]["reverseAllParallelPaymentsOnError"] : ""; 
		
		//Preparing items array
		$orderItems = array();
		$billAmounts = array();
		$netTotal = 0;
		$taxTotal = 0;
		$grandTotal = 0;
		//Grouping the items array based on the merchant ,if more than one merchants are selected
		/*Checking whether each item contains the merchant_email if there are more than one merchant to be paid,then each iteam should
		have merchant_email as item property.If that field is not there it should fail with missing required field error*/
		
		if(count($merchantEmails)==1)
		{			
			$merchantEmail=$merchantEmails[0];
			$orderItems[$merchantEmail]["items"]=array();
						
		}
		else
		{		
			foreach($body["items"] as $item) 
			{	
				$merchantEmail=isset($item["merchant_email"])?$item["merchant_email"]:"";
				if($merchantEmail=="")
				{
					$this->logger->error("Field validation failed,missing required fields");
					return Util::generateErrResponse("error",ERR_MISSING_REQUIRED_FIELDS);
				}
				$merchantEmail=$item["merchant_email"];
				if(!isset($orderItems[$merchantEmail])) 
				{
					$orderItems[$merchantEmail]["items"] = array();
					$netTotal=0;
					$taxTotal=0;
					$grandTotal=0;			
				}
			}
		}		
		foreach($body["items"] as $item) 
		{
			$merchantEmail=(isset($item["merchant_email"]) && $item["merchant_email"] !="")?$item["merchant_email"]:$merchantEmails[0];
							
			$this_item = array(
				'item_name' => isset($item["item_name"]) ? $item["item_name"] : "No Item Name",	// Item Name.  127 char max.
				'item_desc' => isset($item["item_desc"]) ? $item["item_desc"] : "No Description",	// Item description.  127 char max.
				'item_price' => isset($item["item_price"]) ? $item["item_price"] : "0",			// Cost of individual item.
				'item_id' => isset($item["item_id"]) ? $item["item_id"] : "0",			// Item Number.  127 char max.
				'item_qty' => isset($item["item_qty"]) ? $item["item_qty"] : "0",			// Item quantity.  Must be any positive integer.  
				'item_tax' => isset($item["item_tax"]) ? $item["item_tax"] : "0"			// Item's sales tax amount.
			);
			array_push($orderItems[$merchantEmail]["items"], $this_item);
		}
		
		foreach($orderItems as $key => $val) {
			$netTotal=0;
			$taxTotal=0;
			$grandTotal=0;

			foreach($val["items"] as $item)
			{
				$netTotal += (floatval($item["item_price"]) * intval($item["item_qty"]));
				$taxTotal += (floatval($item["item_tax"]) * intval($item["item_qty"]));
				$grandTotal = $netTotal + $taxTotal;
			}
			array_push($billAmounts,$grandTotal);
		}
		if(count($billAmounts) !=count($merchantEmails))
		{
			$this->logger->error("The count of payees given does not match with payee details given in item properties");
			return Util::generateErrResponse('error',ERR_CHECKOUT_FAILED);
		}
	
		//Checking if all the mandatory fields are given in the input data
		$requiredFields = array($this->apiEndPoint,$apiUserName,$apiSignature,
			$appID ,
			$merchantEmails,
			$billAmounts,
			$currencyCode								
		);
		$this->logger->info("Checking whether all required fields are filled");
		if(Util::validateFilled($requiredFields) == false)
		{		
			$this->logger->error("Field validation failed,missing required fields");
			return Util::generateErrResponse('error',ERR_MISSING_REQUIRED_FIELDS);
		}
		
		//Performing checkout
		$this->logger->info("Performing checkout(generating redirect URL)");	
		//Creating PaypalPlatform object and setting all the api credentials before making actual call
		$this->logger->info("Creating PaypalPlatform object and setting all the api credentials before making actual call");
		$this->initPaypalPlatform($this->apiEndPoint,$apiUserName,$apiPassword,$apiSignature,$appID);
		$trackingId=$this->paypalPlatform->generateTrackingID();
		$this->paypalPlatform->setTrackingId($trackingId);		

		//populating the input data array
		$inputData["merchantEmails"] = $merchantEmails;
		$inputData["billAmounts"] = $billAmounts;
		$inputData["currencyCode"] =$currencyCode;
		$inputData["invoiceNumbers"] = $invoiceNumbers;
		$inputData["primaryIndicators"] = $primaryIndicators;
		$inputData["trackingId"] = $trackingId;
		$inputData["returnUrl"] = $returnUrl;
		$inputData["cancelUrl"] = $cancelUrl;
	
		//generating the redirectUrl
		$this->logger->info("generating the redirectUrl");	
		$response = $this->generateRedirectUrl($inputData);
		
		$result = $response->asArray();
		if(isset($result["status"]))
		{
			
			if($result["status"] == "success")
			{	
				$this->logger->debug("Response: ".$result["data"]["redirectUrl"]);					
				return Util::generateResponse("success",$result["data"]);
			}
			else
				return $response;
		}
		else
		{			
			$this->logger->error("Error in generating the redirectUrl");
			return $response;
		}
	}
	

	/**
	 * IPayPalCheckout interface mandate
	 * 
	 * Validates the input data according to PaymentsPro requirements
	 */
	public function validateInputData($req) 
	{
		return true;	 
	}

	/**
	 * getStatus function
	 * 
	 * Internal utility fuinction to get Payment status using paykey
	 */
	public function getStatus($req){
		$this->logger = Logger::getLogger("AdaptivePay");
		$body = $req->asArray();

		//Checking if input data is in proper format
		if(null == $body) {
			$this->logger->error("The input data is not in the specified format");			
			return Util::generateErrResponse('error',ERR_BAD_INPUT);			
		}

		//Checking whether this is live or sandbox call
		try {
			if($body["live"] == TRUE) {
				$this->logger->info("This call is made in live environment");
				$this->init('live');
			} else {
				$this->logger->info("This call is made in sandbox environment");
				$this->init('sandbox');
			}
		} catch(Exception $e) {
			$this->logger->error("Config file exception : ".$e->getMessage());
			return Util::generatErreResponse('error',ERR_INTERNAL_EXCEPTION);		
		}

		//Authentication information
		$apiUserName = isset($body["api_username"]) ? $body["api_username"] : "";
		$apiPassword = isset($body["api_password"]) ? $body["api_password"] : "";	
		$apiSignature = isset($body["api_signature"]) ? $body["api_signature"] : "";
		$apiproxyHost = isset($body["api_proxyhost"]) ? $body["api_proxyhost"] : "";
		$apiproxyPort = isset($body["api_proxyport"]) ? $body["api_proxyport"] : "";
		$appID = isset($body["api_appid"]) ? $body["api_appid"] : "";


		// Specify required fields
		$requiredFields = array(
			$apiUserName, 
			$apiPassword, 
			$apiSignature, 
			$appID		
		);

		// Field validation 
		if( false ==  Util::validateFilled($requiredFields) ) {		
			$this->logger->error("Field validation failed,missing required fields");
			return Util::generateErrResponse("error",ERR_MISSING_REQUIRED_FIELDS);			
		}
		//Creating PaypalPlatform object and setting all the api credentials before making actual call
		$this->initPaypalPlatform($this->apiEndPoint,$apiUserName,$apiPassword,$apiSignature,$appID);
		
		//paykey and/or tracking id is fetched from input data
		$payKey = isset($body["payKey"]) ? $body["payKey"]:"";
		$trackingId = isset($body["trackingId"]) ? $body["trackingId"]:"";
		//Either paykey or tracking id is needed ti fetch the payment status
		if(!isset($body["payKey"]) && !isset($body["trackingId"])) {
			$this->logger->error("Either paykey or tracking id is required");
			return Util::generateErrResponse("error", ERR_MISSING_REQUIRED_FIELDS);
		}
		//calling payment status api
		$resArray = $this->paypalPlatform->callPaymentDetails($payKey,"",$trackingId);
		//checking if the responseEnvelope is returned
		if(isset($resArray["responseEnvelope.ack"]))
		$ack = strtoupper($resArray["responseEnvelope.ack"]);
		else
		{
			$this->logger->error("Getting status operaion failed");
			return Util::generateErrResponse("error", ERR_STATUS_OPERATION_FAILED);
		}
		//chekcing if the status is fetched successfully
		if($ack == "SUCCESS") {
			return Util::generateResponse('success',$this->prepareStatusResponse($resArray));
		} 
		else {

			//Display a user friendly Error on the page using any of the following error information returned by PayPal
			//TODO - There can be more than 1 error, so check for "error(1).errorId", then "error(2).errorId", and so on until you find no more errors.
			$this->logger->error( $ErrorMsg);
			$ErrorCode = urldecode($resArray["error(0).errorId"]);
			$ErrorMsg = urldecode($resArray["error(0).message"]);
			$ErrorDomain = urldecode($resArray["error(0).domain"]);
			$ErrorSeverity = urldecode($resArray["error(0).severity"]);
			$ErrorCategory = urldecode($resArray["error(0).category"]);
			return $ErrorMsg;
		}
	}
	
	/**
	 * generateRedirectUrl function
	 * 
	 * Internal utility fuinction to generate a preApprovalKey
	 */
	private function generateRedirectUrl($inputData) 
	{	
		// Filling the required fields 		
		$actionType			= "PAY"; //This field is used by CallPay function
		$trackingId		    = 	$inputData["trackingId"];// generateTrackingID function is found in paypalplatform.php
		/* If you are not executing the Pay call for a preapproval,
		   then you must set a valid cancelUrl for the web approval flow
		that immediately follows this Pay call*/
		$cancelUrl			= $inputData["cancelUrl"];	
		/*If we are not executing the Pay call for 	a preapproval,then you must set a valid returnUrl for the web approval flow
		that immediately follows this Pay call*/
		$returnUrl			= $inputData["returnUrl"];	

		// specifying the list of email ids of merchants(shops)
		$merchantEmailArray	= $inputData["merchantEmails"];

		// specifying the list of email ids of merchants(shops)
		$currencyCode= $inputData["currencyCode"];

		// specifying the bill amount for each merchant as the amount of money, for example, '5' or '5.55'
		$merchantsBillsAmtArray = $inputData["billAmounts"];

		// for basic payment, no primary indicators are needed, so setting empty array
		$merchantPrimaryArray = $inputData["primaryIndicators"];

		// setting invoiceId to uniquely identify the transaction associated with the merchant
		//	We can set this to the same value as trackingId if we wish
		$merchantInvoiceIdsArray = $inputData[ "invoiceNumbers"];

		// Request specific optional or conditionally required fields
		// Provide a value for each field that you want to include in the request, if left as an empty string the field will not be passed in the request
		$senderEmail		= $inputData["senderEmail"];// If we are executing the Pay call against a preapprovalKey, we should set senderEmail
		// It is not required if the web approval flow immediately follows this Pay call
		$feesPayer		= $inputData["feesPayer"];
		$ipnNotificationUrl	= $inputData["ipnNotificationUrl"];
		$memo			= $inputData["memo"];		// maxlength is 1000 characters
		$pin			= $inputData["pin"];		// If we are executing the Pay call against an existing preapproval
		// the requires a pin, then we must set this
		$preapprovalKey		= $inputData["preApprovalKey"];;		// If we are executing the Pay call against an existing preapproval, we need to set this key           		
		$reverseAllParallelPaymentsOnError	= $inputData["reverseAllParallelPaymentsOnError"];				// Do not specify for basic payment


		// Make the Pay API call
		// The CallPay function is defined in the paypalplatform.php file,
		// which is included at the top of this file.
		$resArray=$this->paypalPlatform->CallPay($actionType, $cancelUrl, $returnUrl, $currencyCode, $merchantEmailArray,
			$merchantsBillsAmtArray, $merchantPrimaryArray, $merchantInvoiceIdsArray,
			$feesPayer, $ipnNotificationUrl, $memo, $pin, $preapprovalKey,
			$reverseAllParallelPaymentsOnError, $senderEmail, $trackingId
		);
		$ack = strtoupper($resArray["responseEnvelope.ack"]);
				
		if($ack=="SUCCESS")
		{
			// payKey is the key that you can use to identify the payment resulting from the Pay call
			$payKey = urldecode($resArray["payKey"]);
			// paymentExecStatus is the status of the payment
			$paymentExecStatus = urldecode($resArray["paymentExecStatus"]);
		}		 
		else  
		{
			//Display a user friendly Error on the page using any of the following error information returned by PayPal
			//TODO - There can be more than 1 error, so check for "error(1).errorId", then "error(2).errorId", and so on until you find no more errors.
			$ErrorCode = urldecode($resArray["error(0).errorId"]);
			$ErrorMsg = urldecode($resArray["error(0).message"]);
			$ErrorDomain = urldecode($resArray["error(0).domain"]);
			$ErrorSeverity = urldecode($resArray["error(0).severity"]);
			$ErrorCategory = urldecode($resArray["error(0).category"]);
			$this->logger->error( $ErrorMsg);
			return Util::generateErrResponse("error", ERR_INTERNAL_EXCEPTION);
		}
		$redirectUrl=$this->endPointURL."?payKey=".$payKey;
		$response= array("payKey" => $payKey,
			"redirectUrl" => $redirectUrl,
			"trackingId" => $trackingId);
		return Util::generateResponse("success",$response);
	}


	/**
	 * Internal method to prepare status response
	 */

	private function prepareStatusResponse($res)
	{		
		$index=0;
		$statusArray=array();
		while(isset($res["paymentInfoList.paymentInfo($index).receiver.email"])) {
			$arr["email"] = $res["paymentInfoList.paymentInfo($index).receiver.email"];
			$arr["status"] = isset($res["paymentInfoList.paymentInfo($index).receiver.transactionStatus"]) ? $res["paymentInfoList.paymentInfo($index).transactionStatus"] : $res["status"];
			$statusArray[$index] = $arr;
			$index = $index+1;
		}
		return $statusArray;	
	}


	private function initPaypalPlatform($apiEndPoint,$apiUserName,$apiPassword,$apiSignature,$appID)
	{	
		$this->paypalPlatform = new PayPalPlatform();
		//Setting all the api credentials before making actual call
		$this->paypalPlatform->setAPIEndPoint($apiEndPoint);
		$this->paypalPlatform->setAPIUserName($apiUserName);
		$this->paypalPlatform->setAPIPassword($apiPassword);
		$this->paypalPlatform->setAPISignature($apiSignature);			
		$this->paypalPlatform->setAppId($appID);		
	}
}
?>
