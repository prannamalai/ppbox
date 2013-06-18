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


/** 
 *  Class EmailCheckout
 *
 *  This class is expert for email based payments
 */
class EmailCheckout {
	private $logger;

	public function setLogger($logger) {
		$this->logger = $logger;
	}

	/**
	 * Function to perform checkout operation using email based payments
	 * it returns the redirection link for caller to complete checkout
	 *
	 * @param BaseRequest $req Object of BaseRequest
	 *
	 * @return BaseResponse  
	 */
	public function doCheckout($req) {

		$this->logger->info("Starting Email Checkout expert flow");

		// Get instance of package class which is relevant here...
		// (see controller.ini file for mappings)

		try {
			$this->logger->info("Using AdaptivePay package");
			PackageFactory::loadPackage('adaptivepay');
			$obj = PackageFactory::getObject('adaptivepay.class');		

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
			$resp->setData(array('text' => ERR_PACKAGE_EXCEPTION));

			return $resp;
		} catch(Exception $e) {
			$this->logger->error("Exception: ".$e->getMessage());

			// Return back data after ptocessing payment
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_INTERNAL_EXCEPTION));

			return $resp;
		}


		// Business logic...
		$resp = $obj->doCheckout($req);
		$this->logger->info("Performed checkout using email");		

		$this->logger->info("Exiting Email checkout expert flow");

		return $resp;
	}
}

?>
