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

include_once(__DIR__.'/../lib/log4php/Logger.php'); // Apache Log4php framework class
Logger::configure(__DIR__.'/../config/log4php_config.xml');

/**
 * Encapsulates the functionality for preparing/manipulating Request objects
 */
class BaseRequest {
	/** @type string Input content type */
	private $contentType;

	/** @type array Input data converted to array */ 
	private $data;

	public function __construct($contentType) {
		$this->contentType = $contentType;
	}
	
	/**
	 * Sets the data of request
	 *
	 * @param array Array that contains data
	 *
	 * @return void
	 */
	public function setData($data) {
		$this->data = $data;
	}

	/**
	 * Sets the data of request
	 *
	 * @param string String that contains data in a specific format indicated by content type
	 *
	 * @return void
	 */
	public function setDataFromString($strData) {
		$logger = Logger::getLogger("BaseRequest");
		try {
			if('json' == $this->contentType) {
				$this->data = json_decode($strData, true);
				if(null == $this->data) {
					// $this->data will be null if json is not decodable
					throw new Exception($this->getLastJsonError());
				}

			} else if('xml' == $this->contentType) {
				// json_encode and json_decode are applied to convert SimpleXMlElement objects into array objects
				// when the input contains nested data
				$this->data = json_decode(json_encode(simplexml_load_string($strData)), true);

				// No need to explicitly check for errors because simplexml_load_string will throw exception
				// in case of any problems 

			} else { // default
				$logger->debug('No "Content-Type" header, defaulted to JSON');
				$this->data = json_decode($strData, true);
				if(null == $this->data) {
					// $this->data will be null if json is not decodable
					throw new Exception($this->getLastJsonError());
				}
			}

		} catch (Exception $e) {
			$logger->error($e->getMessage());

			throw $e;

			// Note: If we can find some way to get localized error strings from simplexml_load_string exceptions,
			// then we can simply return back those strings to the caller, hence making it more informative for him.
			// Right now we just ignore the message in exception and return ERR_BAD_INPUT to the caller.
		}

	}

	/**
	 * Gets the last JSON parsing error
	 *
	 * @param void
	 *
	 * @return string Error message based on last JSON pasring error
	 */
	protected function getLastJsonError() {
		$errText = 'JSON parsing error';
		switch (json_last_error()) {
		case JSON_ERROR_NONE:
			$errText .= ' - No errors';
			break;
		case JSON_ERROR_DEPTH:
			$errText .= ' - Maximum stack depth exceeded';
			break;
		case JSON_ERROR_STATE_MISMATCH:
			$errText .= ' - Underflow or the modes mismatch';
			break;
		case JSON_ERROR_CTRL_CHAR:
			$errText .= ' - Unexpected control character found';
			break;
		case JSON_ERROR_SYNTAX:
			$errText .= ' - Syntax error, malformed JSON';
			break;
		case JSON_ERROR_UTF8:
			$errText .= ' - Malformed UTF-8 characters, possibly incorrectly encoded';
			break;
		default:
			$errText .= ' - Unknown error';
			break;
		}

		return $errText;
	}

	/**
	 * Gets the content type 
	 *
	 * @param void
	 *
	 * @return string Content type of request 
	 */
	public function getContentType() {
		return $this->contentType;
	}

	/**
	 * Gets the data of response
	 *
	 * @param void 
	 *
	 * @return array Data array
	 */
	public function asArray() {
		return $this->data;
	}
}

?>
