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


/** 
 * Class CreditCardCheckout
 *
 * Expert class for credit card based payments
 * 
 */
class CreditCardCheckout {
	private $logger;

	public function setLogger($logger) {
		$this->logger = $logger;
	}

	/** 
	 * Function doCheckout
	 *
	 * @param BaseRequest $baseReq Object of BaseRequest
	 *
	 * @return BaseResponse  
	 */
	public function doCheckout($baseReq) {

		$this->logger->info("Starting Credit Card Checkout expert flow");

		// Get instance of package class which is relevant here...
		// (see controller.ini file for mappings)

		try {
			$this->logger->info("Using Payments Pro package");
			PackageFactory::loadPackage('ppro');
			$obj = PackageFactory::getObject('ppro_nvp.class');

		} catch(PackageNotFoundException $e) {
			$this->logger->error($e->getMessage());
			
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_PACKAGE_EXCEPTION));

			return $resp;
		} catch(ClassNotFoundException $e) {
			$this->logger->error($e->getMessage());
		
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_PACKAGE_EXCEPTION ));

			return $resp;
		} catch(Exception $e) {
			$this->logger->error($e->getMessage());
			
			// Return back data after ptocessing payment
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_INTERNAL_EXCEPTION));

			return $resp;
		}


		// Business logic...
		$resp = $obj->doCheckout($baseReq);


		$this->logger->info("Performed checkout using credit card");		

		$this->logger->info("Exiting Credit Card checkout expert flow");

		return $resp;
	}
}

?>
