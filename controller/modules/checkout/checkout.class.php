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

// Dependencies of this file
// Global framework classes have been loaded already before this class by the framework
chdir(__DIR__);
require_once('creditcard/creditcardcheckout.class.php');
require_once('email/emailcheckout.class.php');
require_once('share2pay/share2paycheckout.class.php');

include_once('../../lib/log4php/Logger.php'); // Apache Log4php framework class

Logger::configure('../../config/log4php_config.xml');

/** 
 * Class Checkout
 * Extends PayPalCallable
 * Mapping from "checkout" alias (see controller.ini file)
 *
 * This class is expected to perform only business logic functions and should delegate the actual
 * work to specific classes defined inside their own folders. eg. "creditcard/"
 */
class CheckOut extends PayPalCallable {

	// No _default handler

	/** 
	 * POST HTTP method handler for credit_card type of checkout
	 * Mapping from "credit_card" alias 
	 *
	 * @param BaseRequest $req Object of BaseRequest
	 *
	 * @return BaseResponse Returns object of BaseResponse
	 */
	public function post_credit_card($req) {

		// Make use of log4php framework for writing to log file
		// (use log4php_config.xml file to enable/disable logs and configure log file path)

		$logger = Logger::getLogger("Checkout");

		try {

			$logger->info("Starting Credit Card Checkout");

			// Create object of expert class and get the work done (instead of doing it here)
			$cccheckout = new CreditCardCheckout();
			$cccheckout->setLogger($logger);
			$resp = $cccheckout->doCheckout($req);

			// After expert is done...
			$logger->info("Credit Card Checkout complete");

			return $resp;
		} catch(Exception $e) {	

			$logger->error($e->getMessage());

			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_INTERNAL_EXCEPTION));

			return $resp;
		}

	}


	/** 
	 * POST HTTP method handler for share-to-pay type of checkout
	 * Mapping from "share2pay" alias 
	 *
	 * @param BaseRequest $req Object of BaseRequest
	 *
	 * @return BaseResponse Returns object of BaseResponse
	 */
	public function post_share2pay($req) {

		$logger = Logger::getLogger("Checkout");

		try {
			$logger->info('Starting Checkout');
			// Create object of expert class and get the work done (instead of doing it here)
			$stpCheckout = new Share2PayCheckout();
			$stpCheckout->setLogger($logger);
			$response = $stpCheckout->doCheckout($req);

			// After expert is done...
			if('success' == $response->getStatus())
			{
				$logger->info("Successfully performed Checkout");
				$logger->info("Exiting checkout flow");
			}
			else
			{
				$logger->error("Checkout operation failed");				
				$logger->error($response->getData());				
				$logger->info("Exiting Checkout flow");
			}
			return $response;			
		} 
		catch(Exception $e) 
		{	
			$logger->error("Checkout operation failed");	
			$logger->error($e->getMessage());
			$logger->info("Exiting Checkout flow");
			$data = array('text' => $e->getMessage());			
			return Util::generateResponse('error',$data);			
		}	
	}	

	/** 
	 * POST HTTP method handler for email based checkout
	 * Mapping from "email" alias 
	 *
	 * @param BaseRequest $req Object of BaseRequest
	 *
	 * @return BaseResponse Returns object of BaseResponse
	 */
	public function post_email($req) {

		$logger = Logger::getLogger("Checkout");

		try {
			$logger->info('Starting Email Checkout');

			// Create object of expert class and get the work done (instead of doing it here)
			$emailCheckout = new EmailCheckout();

			$emailCheckout->setLogger($logger);

			$resp = $emailCheckout->doCheckout($req);

			// After expert is done...
			$logger->info("Email Checkout complete");

			return $resp;
		} catch(Exception $e) {	

			$logger->error("Exception :".$e->getMessage());

			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_INTERNAL_EXCEPTION));	
			return $resp;
		}
	}
}

?>
