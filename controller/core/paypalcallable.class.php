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
 * Base class for all callable actions/modules eg. checkout
 */
abstract class PayPalCallable implements ICallable {
	protected $logger;
	
	/** Invokes a function of self
	 * Particularly useful to invoke implemented methods of child classes from base controller
	 */
	public function invokeFunction($function, $data) {
		return call_user_func(array($this, $function), $data);
	}
	
	/**
	 * Default function called when 'method' parameter supplied in call is incorrects/missing
	 * Concrete class needs to override this function if it requires a default functionality to run
	 * when method parameter is missing/incorrect
	 */ 
	public function _default() {
		$resp = new BaseResponse();
		$resp->setStatus('error');
		$resp->setData('Incorrect or missing parameter \'method\', which is required for this action');
	
		return $resp;
	}
	
	/**
	 * Sets the logger for this class
	 */
	public function setLogger($logger) {
		$this->logger = $logger;
//		$this->logger->setOwnerModule(get_class($this));
	}
}

?>
