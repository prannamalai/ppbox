<?php
/**
 * Copyright (c) 2013 Vaibhav Pujari
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

/**
 * PaymentsPro class
 *
 * Responsible for functionality related to PaymentsPro API
 */
class PaymentsPro implements IPayPalCheckout {
	private $endPointUrl;
	private $logger;	

	/**
	 * Initializer function
	 * 
	 * @throws Exception
	 */
	private function init($target) {

		$ini_array = parse_ini_file('paymentspro.ini', true);

		if($target == 'live') {
			if(isset($ini_array['endpoints']['live'])) {
				$this->endPointUrl = $ini_array['endpoints']['live'];
			} else {
				throw new Exception("Live endpoint undefined");
			}
		} elseif($target == 'sandbox') {
			if(isset($ini_array['endpoints']['sandbox'])) {
				$this->endPointUrl = $ini_array['endpoints']['sandbox'];
			} else {
				throw new Exception("Sandbox endpoint undefined");
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
		
		$this->logger = Logger::getLogger("PaymentsPro");
		$body = $req->asArray();

		if(null == $body || !isset($body['authentication'])) {
			$this->logger->error("Bad input");
			
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_BAD_INPUT));

			return $resp;
		}

		try {
			if($body["authentication"]["live"] == TRUE) {
				$this->logger->debug("Using live endpoint");
				$this->init('live');
			} else {
				$this->logger->debug("Using sandbox endpoint");
				$this->init('sandbox');
			}
		} catch(Exception $e) {
			$this->logger->error("Config file exception : ".$e->getMessage());

			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_INTERNAL_EXCEPTION));

			return $resp;
		}

		// API credentials
		$apiVersion = isset($body["authentication"]["version"]) ? $body["authentication"]["version"] : "95.0";
		$btnSource = isset($body["authentication"]["btn_source"]) ? $body["authentication"]["btn_source"] : "";
		$apiUserName = isset($body["authentication"]["api_username"]) ? $body["authentication"]["api_username"] : "";
		$apiPassword = isset($body["authentication"]["api_password"]) ? $body["authentication"]["api_password"] : "";
		$apiSignature = isset($body["authentication"]["api_signature"]) ? $body["authentication"]["api_signature"] : "";

		// Credit Card credentials
		$ccType = isset($body["payer"]["funding_instrument"]["credit_card"]["type"]) ? $body["payer"]["funding_instrument"]["credit_card"]["type"] : "";
		$ccNumber = isset($body["payer"]["funding_instrument"]["credit_card"]["number"]) ? $body["payer"]["funding_instrument"]["credit_card"]["number"] : "";
		$expiryMonth = isset($body["payer"]["funding_instrument"]["credit_card"]["expire_month"]) ? $body["payer"]["funding_instrument"]["credit_card"]["expire_month"] : "";
		$expiryYear = isset($body["payer"]["funding_instrument"]["credit_card"]["expire_year"]) ? $body["payer"]["funding_instrument"]["credit_card"]["expire_year"] : "";
		$expiryDate = $expiryMonth.$expiryYear;
		$cvv2 = isset($body["payer"]["funding_instrument"]["credit_card"]["cvv2"]) ? $body["payer"]["funding_instrument"]["credit_card"]["cvv2"] : "";
		$ccStartDate = isset($body["payer"]["funding_instrument"]["credit_card"]["start_date"]) ? $body["payer"]["funding_instrument"]["credit_card"]["start_date"] : "";
		$ccIssueNumber = isset($body["payer"]["funding_instrument"]["credit_card"]["issue_number"]) ? $body["payer"]["funding_instrument"]["credit_card"]["issue_number"] : "";


		// Payer details
		$payerEmail = isset($body["payer"]["email"]) ? $body["payer"]["email"] : "";
		$payerFName = isset($body["payer"]["funding_instrument"]["credit_card"]["first_name"]) ? $body["payer"]["funding_instrument"]["credit_card"]["first_name"] : "";
		$payerLName = isset($body["payer"]["funding_instrument"]["credit_card"]["last_name"]) ? $body["payer"]["funding_instrument"]["credit_card"]["last_name"] : "";
		$payerPhone = '';


		// Biling address
		$addrline1 = isset($body["payer"]["funding_instrument"]["credit_card"]["billing_address"]["line1"]) ? $body["payer"]["funding_instrument"]["credit_card"]["billing_address"]["line1"] : "";
		$addrline2 = isset($body["payer"]["funding_instrument"]["credit_card"]["billing_address"]["line2"]) ? $body["payer"]["funding_instrument"]["credit_card"]["billing_address"]["line2"] : "";
		$city = isset($body["payer"]["funding_instrument"]["credit_card"]["billing_address"]["city"]) ? $body["payer"]["funding_instrument"]["credit_card"]["billing_address"]["city"] : "";
		$state = isset($body["payer"]["funding_instrument"]["credit_card"]["billing_address"]["state"]) ? $body["payer"]["funding_instrument"]["credit_card"]["billing_address"]["state"] : "";
		$countryCode = isset($body["payer"]["funding_instrument"]["credit_card"]["billing_address"]["country_code"]) ? $body["payer"]["funding_instrument"]["credit_card"]["billing_address"]["country_code"] : "";
		$zip = isset($body["payer"]["funding_instrument"]["credit_card"]["billing_address"]["zip"]) ? $body["payer"]["funding_instrument"]["credit_card"]["billing_address"]["zip"] : "";


		// Transaction details
		$currency = isset($body["transaction"]["currency"]) ? $body["transaction"]["currency"] : "";
		$trantax = isset($body["transaction"]["tax"]) ? $body["transaction"]["tax"] : "";
		$shipping = isset($body["transaction"]["shipping"]) ? $body["transaction"]["shipping"] : "";
		$transactionDescription = isset($body["transaction"]["description"]) ? $body["transaction"]["description"] : "";
		$invoiceNumber = isset($body["transaction"]["invoice_number"]) ? $body["transaction"]["invoice_number"] : "";

		// Specify required fields
		$requiredFields = array(
			$apiUserName, 
			$apiPassword, 
			$apiSignature, 
			$ccType, 
			$ccNumber, 
			$expiryDate, 
			$cvv2,
			$payerFName,
			$payerLName
		);

		// Field validation 
		if( false ==  Util::validateFilled($requiredFields) ) {
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_MISSING_REQUIRED_FIELDS));

			$this->logger->error("Missing required field(s)");
			return $resp;
		}

		if($this->validateInputData($req) == false) {
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_FIELD_VALIDATION_FAILED));

			$this->logger->error("Field validation failed");
			return $resp;
		}


		// Auth fields 
		$authFields = array(
			'user' => $apiUserName,
			'pwd' => $apiPassword,
			'version' => $apiVersion,
			'buttonsource' => $btnSource,
			'signature' => $apiSignature,
		);

		// Prepare request arrays
		$dpFields = array(
			'paymentaction' => $body["intent"],		// How you want to obtain payment.  Authorization indidicates the payment is a basic auth subject to settlement with Auth & Capture.  Sale indicates that this is a final sale for which you are requesting payment.  Default is Sale.
			'ipaddress' => $_SERVER['REMOTE_ADDR'], 	// Required.  IP address of the payer's browser.
			'returnfmfdetails' => '0' 			// Flag to determine whether you want the results returned by FMF.  1 or 0.  Default is 0.
		);

		$ccDetails = array(
			'creditcardtype' => $ccType, 			// Required. Type of credit card.  Visa, MasterCard, Discover, Amex, Maestro, Solo.  If Maestro or Solo, the currency code must be GBP.  In addition, either start date or issue number must be specified.
			'acct' => $ccNumber,				// Required.  Credit card number.  No spaces or punctuation.  
			'expdate' => $expiryDate,				// Required.  Credit card expiration date.  Format is MMYYYY
			'cvv2' => $cvv2, 				// Requirements determined by your PayPal account settings.  Security digits for credit card.
			'startdate' => $ccStartDate, 			// Month and year that Maestro or Solo card was issued.  MMYYYY
			'issuenumber' => $ccIssueNumber		// Issue number of Maestro or Solo card.  Two numeric digits max.
		);

		$payerInfo = array(
			'email' => $payerEmail,				// Email address of payer.
			'firstname' => $payerFName,			// Required.  Payer's first name.
			'lastname' => $payerLName 			// Required.  Payer's last name.
		);

		$billingAddress = array(
			'street' => $addrline1,				// Required.  First street address.
			'street2' => $addrline2,			// Second street address.
			'city' => $city,				// Required.  Name of City.
			'state' => $state, 				// Required. Name of State or Province.
			'countrycode' => $countryCode,			// Required.  Country code.
			'zip' => $zip, 					// Required.  Postal code of payer.
			'phonenum' => $payerPhone 			// Phone Number of payer.  20 char max.
		);

		$shippingAddress = array(
			'shiptoname' => '', 				// Required if shipping is included.  Person's name associated with this address.  32 char max.
			'shiptostreet' => '', 				// Required if shipping is included.  First street address.  100 char max.
			'shiptostreet2' => '', 				// Second street address.  100 char max.
			'shiptocity' => '', 				// Required if shipping is included.  Name of city.  40 char max.
			'shiptostate' => '', 				// Required if shipping is included.  Name of state or province.  40 char max.
			'shiptozip' => '', 				// Required if shipping is included.  Postal code of shipping address.  20 char max.
			'shiptocountry' => '', 				// Required if shipping is included.  Country code of shipping address.  2 char max.
			'shiptophonenum' => ''				// Phone number for shipping address.  20 char max.
		);

		// Loop through items array to populate orderItems array abd calculate totals
		$orderItems = array();
		$netTotal = 0;
		$taxTotal = 0;
		$grandTotal = 0;

		foreach($body["items"] as $item) {
			$this_item = array(
				'l_name' => isset($item["item_name"]) ? $item["item_name"] : "No Item Name",	// Item Name.  127 char max.
				'l_desc' => isset($item["item_desc"]) ? $item["item_desc"] : "No Description",	// Item description.  127 char max.
				'l_amt' => isset($item["item_price"]) ? $item["item_price"] : "0",			// Cost of individual item.
				'l_number' => isset($item["item_id"]) ? $item["item_id"] : "0",			// Item Number.  127 char max.
				'l_qty' => isset($item["item_qty"]) ? $item["item_qty"] : "0",			// Item quantity.  Must be any positive integer.  
				'l_taxamt' => isset($item["item_tax"]) ? $item["item_tax"] : "0",			// Item's sales tax amount.
				'l_ebayitemnumber' => '',							// eBay auction number of item.
				'l_ebayitemauctiontxnid' => '', 						// eBay transaction ID of purchased item.
				'l_ebayitemorderid' => '' 							// eBay order ID for the item.
			);

			array_push($orderItems, $this_item);

//			$netTotal += (floatval($item["item_price"]) * intval($item["item_qty"]));
//			$taxTotal += (floatval($item["item_tax"]) * intval($item["item_qty"]));
			$netTotal += (floatval($this_item['l_amt']) * intval($this_item['l_qty']));
			$taxTotal += (floatval($this_item['l_taxamt']) * intval($this_item['l_qty']));
		}

		$grandTotal = $netTotal + $taxTotal + floatval($shipping);


		$paymentDetails = array(
			'amt' => strval($grandTotal),			// Required.  Total amount of order, including shipping, handling, and tax.  
			'currencycode' => $currency,			// Required.  Three-letter currency code.  Default is USD.
			'itemamt' => strval($netTotal),			// Required if you include itemized cart details. (L_AMTn, etc.)  Subtotal of items not including S&H, or tax.
			'shippingamt' => $shipping,			// Total shipping costs for the order.  If you specify shippingamt, you must also specify itemamt.
			'insuranceamt' => '', 				// Total shipping insurance costs for this order.  
			'shipdiscamt' => '', 				// Shipping discount for the order, specified as a negative number.
			'handlingamt' => '', 				// Total handling costs for the order.  If you specify handlingamt, you must also specify itemamt.
			'taxamt' => strval($taxTotal),			// Required if you specify itemized cart tax details. Sum of tax for all items on the order.  Total sales tax. 
			'desc' => $transactionDescription,		// Description of the order the customer is purchasing.  127 char max.
			'custom' => '', 				// Free-form field for your own use.  256 char max.
			'invnum' => $invoiceNumber, 			// Your own invoice or tracking number
			'notifyurl' => '', 				// URL for receiving Instant Payment Notifications.  This overrides what your profile is set to use.
			'recurring' => ''				// Flag to indicate a recurring transaction.  Value should be Y for recurring, or anything other than Y if it's not recurring.  To pass Y here, you must have an established billing agreement with the buyer.
		);


		$secure3D = array(
			'authstatus3d' => '', 
			'mpivendor3ds' => '', 
			'cavv' => '', 
			'eci3ds' => '', 
			'xid' => ''
		);

		$paypalRequestData = array(
			'authFields' => $authFields,
			'dpFields' => $dpFields, 
			'ccDetails' => $ccDetails, 
			'payerInfo' => $payerInfo, 
			'billingAddress' => $billingAddress, 
			'shippingAddress' => $shippingAddress, 
			'paymentDetails' => $paymentDetails, 
			'orderItems' => $orderItems
		);

		$this->logger->debug("Performing checkout...");

		// Pass data into class for processing with PayPal and load the response array into $paypalResult
		$paypalResult = $this->doDirectPayment($paypalRequestData);

		$resp = new BaseResponse();
		if($paypalResult['ACK'] == 'Success') {
			$resp->setStatus('success');
			$resp->setData(array('transaction_id' => $paypalResult['TRANSACTIONID']));

			$this->logger->debug("Checkout successful. Transaction ID = ".$paypalResult['TRANSACTIONID']);
		} else {
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_CHECKOUT_FAILED));

			$this->logger->error("Checkout failed. Message from API : ".$paypalResult['L_LONGMESSAGE0']);
		}

		return $resp;
	}

	/**
	 * PaymentsPro DoDirectPayment API wrapper
	 */
	private function doDirectPayment($dataArray)
	{
		// Create empty holders for each portion of the NVP string
		$authFieldsNVP = '';
		$dpFieldsNVP = '&METHOD=DoDirectPayment';
		$ccDetailsNVP = '';
		$payerInfoNVP = '';
		$payerNameNVP = '';
		$billingAddressNVP = '';
		$shippingAddressNVP = '';
		$paymentDetailsNVP = '';
		$orderItemsNVP = '';
		$secure3DNVP = '';

		// Authentication fields	
		$authFields = isset($dataArray['authFields']) ? $dataArray['authFields'] : array();
		$count = 0;
		foreach($authFields as $suthFieldsVar => $authFieldsVal)
		{
			if($count == 0) {
				$authFieldsNVP.= $suthFieldsVar != '' ? strtoupper($suthFieldsVar) . '=' . urlencode($authFieldsVal) : '';
				$count++;
			} else {
				$authFieldsNVP.= $suthFieldsVar != '' ? '&' . strtoupper($suthFieldsVar) . '=' . urlencode($authFieldsVal) : '';
			}
		}

		// DP Fields
		$dpFields = isset($dataArray['dpFields']) ? $dataArray['dpFields'] : array();
		foreach($dpFields as $dpFieldsVar => $dpFieldsVal)
		{
			$dpFieldsNVP .= $dpFieldsVal != '' ? '&' . strtoupper($dpFieldsVar) . '=' . urlencode($dpFieldsVal) : '';
		}

		// CC Details Fields
		$ccDetails = isset($dataArray['ccDetails']) ? $dataArray['ccDetails'] : array();
		foreach($ccDetails as $ccDetailsVar => $ccDetailsVal)
		{
			$ccDetailsNVP .= $ccDetailsVal != '' ? '&' . strtoupper($ccDetailsVar) . '=' . urlencode($ccDetailsVal) : '';
		}

		// Payer information Type Fields
		$payerInfo = isset($dataArray['payerInfo']) ? $dataArray['payerInfo'] : array();
		foreach($payerInfo as $payerInfoVar => $payerInfoVal)
		{
			$payerInfoNVP .= $payerInfoVal != '' ? '&' . strtoupper($payerInfoVar) . '=' . urlencode($payerInfoVal) : '';
		}

		// Payer Name Fields
		$PayerName = isset($dataArray['PayerName']) ? $dataArray['PayerName'] : array();
		foreach($PayerName as $payerNameVar => $payerNameVal)
		{
			$payerNameNVP .= $payerNameVal != '' ? '&' . strtoupper($payerNameVar) . '=' . urlencode($payerNameVal) : '';
		}

		// Address Fields (Billing)
		$billingAddress = isset($dataArray['billingAddress']) ? $dataArray['billingAddress'] : array();
		foreach($billingAddress as $billingAddressVar => $billingAddressVal)
		{
			$billingAddressNVP .= $billingAddressVal != '' ? '&' . strtoupper($billingAddressVar) . '=' . urlencode($billingAddressVal) : '';
		}

		// Payment Details Type Fields
		$paymentDetails = isset($dataArray['paymentDetails']) ? $dataArray['paymentDetails'] : array();
		foreach($paymentDetails as $paymentDetailsVar => $paymentDetailsVal)
		{
			$paymentDetailsNVP .= $paymentDetailsVal != '' ? '&' . strtoupper($paymentDetailsVar) . '=' . urlencode($paymentDetailsVal) : '';
		}

		// Payment Details Item Type Fields
		$orderItems = isset($dataArray['orderItems']) ? $dataArray['orderItems'] : array();
		$n = 0;
		foreach($orderItems as $orderItemsVar => $orderItemsVal)
		{
			$currentItem = $orderItems[$orderItemsVar];
			foreach($currentItem as $currentItemVar => $currentItemVal)
			{
				$orderItemsNVP .= $currentItemVal != '' ? '&' . strtoupper($currentItemVar) . $n . '=' . urlencode($currentItemVal) : '';
			}
			$n++;
		}

		// Ship To Address Fields
		$shippingAddress = isset($dataArray['shippingAddress']) ? $dataArray['shippingAddress'] : array();
		foreach($shippingAddress as $shippingAddressVar => $shippingAddressVal)
		{
			$shippingAddressNVP .= $shippingAddressVal != '' ? '&' . strtoupper($shippingAddressVar) . '=' . urlencode($shippingAddressVal) : '';
		}

		// 3D Secure Fields
		$secure3D = isset($dataArray['secure3D']) ? $dataArray['secure3D'] : array();
		foreach($secure3D as $secure3DVar => $secure3DVal)
		{
			$secure3DNVP .= $secure3DVal != '' ? '&' . strtoupper($secure3DVar) . '=' . urlencode($secure3DVal) : '';
		}

		// Now that we have each chunk we need to go ahead and append them all together for our entire NVP string
		$nvpRequest = $authFieldsNVP . $dpFieldsNVP . $ccDetailsNVP . $payerInfoNVP . $payerNameNVP . $billingAddressNVP . $paymentDetailsNVP . $orderItemsNVP . $shippingAddressNVP . $secure3DNVP;
		$nvpResponse = Util::curlRequest($this->endPointUrl, array(), "POST", $nvpRequest); 

		$nvpRequestArray = $this->nvpToArray($nvpRequest);
		$nvpResponseArray = $this->nvpToArray($nvpResponse);

		$errors = $this->getErrors($nvpResponseArray);

		$nvpResponseArray['ERRORS'] = $errors;
		$nvpResponseArray['REQUESTDATA'] = $nvpRequestArray;
		$nvpResponseArray['RAWREQUEST'] = $nvpRequest;
		$nvpResponseArray['RAWRESPONSE'] = $nvpResponse;

		return $nvpResponseArray;

	}

	/**
	 * IPayPalCheckout interface mandate
	 * 
	 * Validates the input data according to PaymentsPro requirements
	 */
	public function validateInputData($req) {
		$this->logger->debug("Validation successful for input data");
		return true;	//TODO: Perform validation and return correct result;
	}

	/**
	 * Convert an NVP string to an array with URL decoded values
	 *
	 * @access	public
	 * @param	string	NVP string
	 * @return	array
	 */
	protected function nvpToArray($nvpString)
	{
		$proArray = array();
		while(strlen($nvpString))
		{
			// name
			$keypos= strpos($nvpString,'=');
			$keyval = substr($nvpString,0,$keypos);
			// value
			$valuepos = strpos($nvpString,'&') ? strpos($nvpString,'&') :  strlen($nvpString);
			$valval = substr($nvpString,$keypos+1,$valuepos-$keypos-1);
			// decoding the respose
			$proArray[$keyval] = urldecode($valval);
			$nvpString = substr($nvpString,$valuepos+1,strlen($nvpString));
		}

		return $proArray;

	}

	/**
	 * Get all errors returned from PayPal
	 *
	 * @access	public
	 * @param	array	PayPal NVP response
	 * @return	array
	 */
	protected function getErrors($dataArray)
	{

		$errors = array();
		$n = 0;
		while(isset($dataArray['L_ERRORCODE' . $n . '']))
		{
			$lErrorCode = isset($dataArray['L_ERRORCODE' . $n . '']) ? $dataArray['L_ERRORCODE' . $n . ''] : '';
			$lShortMessage = isset($dataArray['L_SHORTMESSAGE' . $n . '']) ? $dataArray['L_SHORTMESSAGE' . $n . ''] : '';
			$lLongMessage = isset($dataArray['L_LONGMESSAGE' . $n . '']) ? $dataArray['L_LONGMESSAGE' . $n . ''] : '';
			$lSeverityCode = isset($dataArray['L_SEVERITYCODE' . $n . '']) ? $dataArray['L_SEVERITYCODE' . $n . ''] : '';

			$currentItem = array(
				'L_ERRORCODE' => $lErrorCode, 
				'L_SHORTMESSAGE' => $lShortMessage, 
				'L_LONGMESSAGE' => $lLongMessage, 
				'L_SEVERITYCODE' => $lSeverityCode
			);

			array_push($errors, $currentItem);
			$n++;
		}

		return $errors;

	}
}
?>
