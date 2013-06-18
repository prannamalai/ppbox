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
 * Encapsulates the functionality for preparing/manipulating Response objects
 */
class BaseResponse {
	/** @type string|null Status, which can be 'success' or 'error' */
	private $status = '';
	/** @type json|null data, specific to the response */ 
	private $data = '';
	
	/**
	 * Sets the status of response
	 *
	 * @param string $description status can be either 'success' or 'error'
	 *
	 * @return void
	 */
	public function setStatus($stat) {
		$this->status = $stat;
	}
	

	/**
	 * Gets the status of response
	 *
	 * @param void 
	 *
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}
	

	/**
	 * Sets the data of response
	 *
	 * @param json $description The json data pertinent to the response 
	 *
	 * @return void
	 */
	public function setData($data) {
		$this->data = $data;
	}
	

	/**
	 * Gets the data of response
	 *
	 * @param void 
	 *
	 * @return json $description The json data pertinent to the response 
	 */
	public function getData() {
		return $this->data;
	}
	/**
	 * Gets the text data of response
	 *
	 * @param void 
	 *
	 * @return text
	 */
	public function getRawData() {
		return isset($this->data["text"])?$this->data["text"]:$this->data;
	}
	

	/**
	 * Gets the respose object as a json 
	 *
	 * @param void 
	 *
	 * @return json The json object prepared from the response 
	 */
	public function asJson() {
		return json_encode($this->asArray(), JSON_UNESCAPED_SLASHES);
	}
	
	/**
	 * Gets the respose object as xml
	 *
	 * @param void 
	 *
	 * @return xml The xml object prepared from the response 
	 */
	public function asXml() {
		$arr_resp = $this->asArray();
		$xml = new SimpleXMLElement('<root/>');
		$func = create_function('$func,$root,$arr','
			foreach($arr as $key => $val) {
				if(is_array($val)) {
					$child = $root->addChild($key);
					$func($func,$child,$val);
				} else {
					$root->addChild($key,$val);
				}
			}');
		$func($func,$xml,$arr_resp); 
		
		return $xml->asXML();
	}

	/**
	 * Gets the respose object as array
	 *
	 * @param void 
	 *
	 * @return array The array object prepared from the response 
	 */
	public function asArray() {
		$arr_resp = array(
			'status' => $this->status, 
			'data' => $this->data
		);
		return $arr_resp;
	}
}

?>
