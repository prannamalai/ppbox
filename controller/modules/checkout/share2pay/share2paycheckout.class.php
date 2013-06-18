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
 * Class ShareToPayCheckout
 *
 * Expert class for payments using share2pay checkout
 * 
 */
class Share2PayCheckout 
 {
	private $logger;

	public function setLogger($logger)
	{
		$this->logger = $logger;
	}

	/** 
	 * Function doCheckout
	 *
	 * @param BaseRequest $req Object of BaseRequest
	 *
	 * @return BaseResponse  
	 */
	public function doCheckOut($req) {

		$this->logger->info("Starting ".SHARE2PAY_PACKAGE_NAME." Checkout expert flow");
		
		try {
			$this->logger->info("Using ".SHARE2PAY_PACKAGE_NAME." package");			
			PackageFactory::loadPackage(SHARE2PAY_PACKAGE_NAME);			
			$obj = PackageFactory::getObject(SHARE2PAY_CLASS_NAME);
		} 
		catch(PackageNotFoundException $e) 
		{
			$this->logger->error($e->getMessage());
			
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_PACKAGE_EXCEPTION));

			return $resp;
		} 
		catch(ClassNotFoundException $e) 
		{
			$this->logger->error($e->getMessage());
			
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_PACKAGE_EXCEPTION));

			return $resp;
		} 
		catch(Exception $e) 
		{
			$this->logger->error($e->getMessage());
			
			// Return back data after ptocessing payment
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_INTERNAL_EXCEPTION));

			return $resp;
		}
		// Business logic...
		$response = $obj->doCheckOut($req);

		//checking if checkout is successful
		if('success' == $response->getStatus())
		{
			$this->logger->info("Successfully performed".SHARE2PAY_PACKAGE_NAME." checkout");	
			$this->logger->info("Exiting ".SHARE2PAY_PACKAGE_NAME. "checkout expert flow");
		}
		else
		{	
			//checkout failed
			$this->logger->error(SHARE2PAY_PACKAGE_NAME." operation failed");			
			$this->logger->error($response->getData());			
			$this->logger->info("Exiting ".SHARE2PAY_PACKAGE_NAME." checkout expert flow");
		}
		return $response;
	}
}

?>
