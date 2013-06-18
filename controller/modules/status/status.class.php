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

// Dependencies of this file
// Global framework classes have been loaded already before this class by the framework
chdir(__DIR__);
include_once('../../lib/log4php/Logger.php'); // Apache Log4php framework class

Logger::configure('../../config/log4php_config.xml');

/** 
 * Class Status
 * Extends PayPalCallable
 * Mapping from "status" alias (see controller.ini file)
 *
 */
class Status extends PayPalCallable
{	

	/** 
	 * GET HTTP method handler for getting status
	 * Mapping from "email" alias 
	 *
	 * @param BaseRequest $req Object of BaseRequest
	 *
	 * @return BaseResponse
	 */
	public function get_email($req) 
	{	
		$logger=Logger::getLogger("Status");		
		$this->setLogger($logger);
		$this->logger->info("Starting get Status expert flow");

		try {
			$this->logger->info("Using adaptive package");			
			PackageFactory::loadPackage("adaptivepay");			
			$obj = PackageFactory::getObject("adaptivepay.class");
		} 
		catch(PackageNotFoundException $e) 
		{
			$this->logger->error($e->getMessage());

			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_PACKAGE_EXCEPTION ));

			return $resp;
		} 
		catch(ClassNotFoundException $e) 
		{
			$this->logger->error($e->getMessage());

			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_PACKAGE_EXCEPTION ));

			return $resp;
		} 
		catch(Exception $e) 
		{
			$this->logger->error($e->getMessage());

			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData(array('text' => ERR_INTERNAL_EXCEPTION));

			return $resp;
		}
		// Business logic...
		$response = $obj->getStatus($req);

		$this->logger->info("Exiting get Status expert flow");
		return $response;
	}

}

?>
