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
 * ClickPay class - responsible for functionality related to email based payment
 */
class ClickPay implements IPayPalCheckout {

	private $endPointURL;
	private $logger;
	private $iniFileName = 'clickpay.ini';

	/**
	 * Initializer function
	 * 
	 * @throws Exception
	 */
	private function init($target) {
		$ini_array = parse_ini_file($this->iniFileName, true);

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
		
		$this->logger = Logger::getLogger(__CLASS__);
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

		if($this->validateInputData($req) == false) {
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_FIELD_VALIDATION_FAILED)); 
			$this->logger->error("Field validation failed");
			return $resp;
		}


		// Payee information
		$merchantEmail = isset( $body["payee"]["email"]) ? $body["payee"]["email"] : ""; 
		// Transaction information
		$currency = isset( $body["transaction"]["currency"] ) ? $body["transaction"]["currency"] : "";
		$invoiceNumber = isset( $body["transaction"]["invoice_number"] ) ? $body["transaction"]["invoice_number"] : "";

//		$orderItems = array();
		$netTotal = 0;
		$taxTotal = 0;
		$grandTotal = 0;

		foreach($body["items"] as $item) {
			$this_item = array(
				'item_name' => isset($item["item_name"]) ? $item["item_name"] : "No Item Name",	// Item Name.  127 char max.
				'item_desc' => isset($item["item_desc"]) ? $item["item_desc"] : "No Description",	// Item description.  127 char max.
				'item_price' => isset($item["item_price"]) ? $item["item_price"] : "0",			// Cost of individual item.
				'item_id' => isset($item["item_id"]) ? $item["item_id"] : "0",			// Item Number.  127 char max.
				'item_qty' => isset($item["item_qty"]) ? $item["item_qty"] : "0",			// Item quantity.  Must be any positive integer.  
				'item_tax' => isset($item["item_tax"]) ? $item["item_tax"] : "0"			// Item's sales tax amount.
			);

//			array_push($orderItems, $this_item);

			$netTotal += (floatval($item["item_price"]) * intval($item["item_qty"]));
			$taxTotal += (floatval($item["item_tax"]) * intval($item["item_qty"]));
		}

		$grandTotal = $netTotal + $taxTotal;

		// Aggregating item information because summary will contain only single item
		if(1 == count($body['items'])) { // Single item
			$itemName = $body['items'][0]['item_name'];
		} else {
			$itemName = AGGREGATED_ITEMS;
		}

		$requiredFields = array($merchantEmail, $itemName, $grandTotal);
		if( false ==  Util::validateFilled($requiredFields) ) {
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_MISSING_REQUIRED_FIELDS));
			$this->logger->error("Missing required field(s)");
			return $resp;
		}


		$this->logger->info("Performing checkout(generating redirect URL)");
	
		$redirectUrl = $this->generateRedirectUrl($merchantEmail, $itemName, $grandTotal, $currency);
		$this->logger->debug("Redirect URl = ".$redirectUrl);
		$resp = new BaseResponse();
		$resp->setStatus('success');
		$resp->setData(array(
			'invoice_number' => $invoiceNumber,
			'redirect_url' => $redirectUrl 
			)
		);

		return $resp;

	}


	/**
	 * generateRedirectUrl function
	 * 
	 * Internal utility fuinction to generate a clickpay url
	 */
	private function generateRedirectUrl($merchantEmail, $itemName, $amount, $currencyCode = 'USD', $itemNumber = '1') {
		return $this->endPointURL.'/cgi-bin/webscr?cmd=_xclick'
			.'&business='.$merchantEmail
			.'&item_name='.urlencode($itemName)
			.'&item_number='.$itemNumber
			.'&amount='.$amount
			.'&currency_code='.$currencyCode;
	}


	/**
	 * IPayPalCheckout interface mandate
	 * 
	 * Validates the input data according to PaymentsPro requirements
	 */
	public function validateInputData($req) {
		return true;	 // TODO: Perform actual validation here 
	}
}
?>
