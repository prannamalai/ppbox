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

chdir(__DIR__);
include_once('../paypalcheckout.interface.php');

/**
 * Share2Pay class - responsible for functionality related to Share2Pay API 
 */
class Share2Pay implements IPayPalCheckout {

	private $endPointURL;
	private $logger;

	/**
	 * Initializer function
	 * 
	 * @throws Exception
	 */
	private function init($target) {
		$this->logger = Logger::getLogger("Share2Pay");
		$ini_array = parse_ini_file('Share2Pay.ini', true);
		if($target == 'live') {
			if(isset($ini_array['endpoints']['live'])) {
				$this->endPointURL = $ini_array['endpoints']['live'];
			} else {
				throw new Exception("Live endpoint undefined");
			}
		} elseif($target == 'sandbox') {
			if(isset($ini_array['endpoints']['sandbox'])) {
				$this->endPointURL = $ini_array['endpoints']['sandbox'];
			} else {
				throw new Exception("Sandbox endpoint undefined");
			}
		}
	}

	/**
	 * IPayPalCheckout interface mandate
	 * 
	 * Do the checkout using input data
	 *
	 * @access	public
	 * @param	BaseRequest	
	 * @return	BaseResponse	
	 */
	public function doCheckout($req) {
	$this->logger = Logger::getLogger("Share2Pay");

		if($this->validateInputData($req) == false) 
		{	
			$this->logger->error("Field validation failed,missing required fields");
			return Util::generateErrResponse('error',ERR_MISSING_REQUIRED_FIELDS);	
		}
		$body = $req->asArray();

		if($body == NULL)
			{	
				$this->logger->error("The input data format is invalid");
				return Util::generateErrResponse('error',ERR_BAD_DATA_FORMAT); 
			}
		try {
			if($body["authentication"]["live"] == TRUE) {
				$this->init('live');
			} else {
				$this->init('sandbox');
			}
		} catch(Exception $e) {
			$this->logger->error("Config file exception : ".$e->getMessage());
			return Util::generateErrResponse('error',ERR_INTERNAL_EXCEPTION);			
		}
		$this->logger->info("Extracting the data fields from the input data");
		// Payee information
		$eventName= isset($body["payee"]["eventname"]) ? $body["payee"]["eventname"] : "";
		$businessDesc= isset($body["payee"]["businessdesc"]) ? $body["payee"]["businessdesc"] : "";
		$beneficiaryEmail= isset($body["payee"]["email"]) ? $body["payee"]["email"] : "";
		$requesterName = isset($body["payee"]["creator"]) ? $body["payee"]["creator"] : "";

		// Payer information
		$payersList = isset( $body["payer"]["funding_instrument"]["share2pay"]["payerslist"] ) ? $body["payer"]["funding_instrument"]["share2pay"]["payerslist"]:"";
		$from = isset( $body["payer"]["funding_instrument"]["share2pay"]["from"] ) ? $body["payer"]["funding_instrument"]["share2pay"]["from"]:"";
		
		$orderItems = array();
		$netTotal = 0;
		$taxTotal = 0;
		$grandTotal = 0;
		//Looping through all items given in the input and giving default value if no value is specified for that field
		foreach($body["items"] as $item) {
			$this_item = array(
				'item_name' => isset($item["item_name"])?$item["item_name"]:"No Item Name",	// Item Name.  127 char max.
				'item_desc' => isset($item["item_desc"])?$item["item_desc"]:"No Description",	// Item description.  127 char max.
				'item_price' => isset($item["item_price"])?$item["item_price"]:"0",			// Cost of individual item.
				'item_id' => isset($item["item_id"])?$item["item_id"]:"0",			// Item Number.  127 char max.
				'item_qty' => isset($item["item_qty"])?$item["item_qty"]:"0",			// Item quantity.  Must be any positive integer.  
				'item_tax' => isset($item["item_tax"])?$item["item_tax"]:"0"			// Item's sales tax amount.
			);

		array_push($orderItems, $this_item);
			//calculating total amount and tax amount
			$netTotal += (floatval($item["item_price"]) * intval($item["item_qty"]));
			$taxTotal += (floatval($item["item_tax"]) * intval($item["item_qty"]));
		}
		$this->logger->info("Calculating the grandTotal amount");
		$grandTotal = $netTotal + $taxTotal;

		// Aggregating item information because summary will contain only single item
		if(1 == count($body['items'])) { // Single item
			$itemName = $body['items'][0]['item_name'];
		} else {
			$itemName = AGGREGATED_ITEMS;
		}
		// Payment information
		$deadLine= isset( $body["transaction"]["deadline"] ) ? $body["transaction"]["deadline"]:"";
		$Amount = $grandTotal;
		$minAmt = isset( $body["transaction"]["minamount"] ) ? $body["transaction"]["minamount"]:"";
		$maxAmt = isset( $body["transaction"]["maxamount"] ) ? $body["transaction"]["maxamount"]:"";
		$anony = isset( $body["transaction"]["anony"] ) ? $body["transaction"]["anony"]:"";
		$handle= isset( $body["transaction"]["handle"] ) ? $body["transaction"]["handle"]:"";
		$this->logger->info("Performing checkout -the pay url will be sent to the payees");
	
		$this->logger->info("Preparing data array");
		// Preparing data array
		$dataArray = array(
		'ename' => $eventName,
		'epart' => $payersList ,
		'ecreator' => $beneficiaryEmail,
		'from'  => $from,
		'deadline' => $deadLine,
		'edesc' => $businessDesc,
		'budget' => intval($Amount),
		'min' => intval($Amount),
		'max' => intval($Amount),
		'anony'	=> 1,
		'handle' => "",
		'save_send' => 1,
		'beneficiary'=> $beneficiaryEmail);
		
		$this->logger->info("Performing validation -checking whether all the required fields are there in the input");
		//Filling required fields array
		$requiredFields=array($eventName,
								$payersList,
								$businessDesc,
								$Amount,
								$beneficiaryEmail);
		//checking if all required fields are given in the input
		if(Util::validateFilled($requiredFields) == false)
		{		
			$this->logger->error(ERR_MISSING_REQUIRED_FIELDS);
			return Util::generateErrResponse('error',ERR_MISSING_REQUIRED_FIELDS);
		}
		//preparing query string from data array
		$queryString=$this->prepareQueryString($dataArray);
		
		if($queryString=="")
			return Util::generateErrResponse('error',ERR_INTERNAL_ERROR);
		//calling share2pay api
		$this->logger->info("Calling share2pay api :".$this->endPointURL);
		$response = Util::curlRequest($this->endPointURL, array(), 'POST',$queryString);	
		//decoding the result
		$result=json_decode($response,true);
		//checking if handle is there in the result which means api call is successful
		if(!isset($result["handle"]))
		{	
			$this->logger->error("Checkout failed");
			return Util::generateErrResponse('error',ERR_CHECKOUT_FAILED);			
		}
		return Util::generateResponse('success',array('handle' => $result["handle"]));	
	}
	
	
	/**
	 * IPayPalCheckout interface mandate
	 * 
	 * Validates the input data according to PaymentsPro requirements
	 */
	public function validateInputData($req) {
		return true;	 // TODO: Perform actual validation here 
	}	
	/**
	 * prepareQueryString
	 * 
	 * Internal method to prepare the query string from array
	 *
	 * @access	public
	 * @param	array	
	 * @return	string	
	 */	
	private function prepareQueryString($dataArray)
	{	
		$queryString="";
		//Extracting only the key fields from the input array
		$urlParams=array_keys($dataArray);
		for($index=0;$index<count($dataArray)-1;$index++)
		{	
			$queryString=$queryString.$urlParams[$index].EQUALS.$dataArray[$urlParams[$index]].AMPERSAND;
		}
		$queryString=$queryString.$urlParams[$index].EQUALS.$dataArray[$urlParams[$index]];
		return $queryString;
	}
}
?>
