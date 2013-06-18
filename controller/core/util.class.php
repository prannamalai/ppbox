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
 * Utility class for use by various packages and modules
 * There is no need to create object of this class because all methods are static
 */
class Util {

	/**
	 * Recursive function to load all php files within a given library
	 */
	public static function loadAllFilesFromDirectory($path) {	
		if ($handle = opendir($path)) {
			while (false !== ($entry = readdir($handle))) {
				if($entry == '.' || $entry == '..') continue;
				
				if(!is_dir($path.'/'.$entry)) {
					if(substr($entry, -4, 4) == '.php') {	// Only include files which are having .php as suffix
						include_once($path.$entry);
					}
				} else {
					chdir($path.$entry.'/');
					self::loadAllFilesFromDirectory($path.$entry.'/');
				}
			}
			closedir($handle);
		}
	}
	
	/**
	 * Function to make a CURL request
	 */
	public static function curlRequest($url, $aHeader, $method = 'GET', $postvals = null){
		$ch = curl_init($url);

		if ($method == 'GET'){
			$options = array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSLVERSION => 3,
				CURLOPT_HTTPHEADER => $aHeader ,
				CURLOPT_SSL_VERIFYPEER => false
			);
			curl_setopt_array($ch, $options);

		} else {
			$options = array(
				CURLOPT_URL => $url,
				CURLOPT_POST => 1,
				CURLOPT_VERBOSE => 1,
				CURLOPT_POSTFIELDS => $postvals,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSLVERSION => 3,
				CURLOPT_HTTPHEADER => $aHeader,
				CURLOPT_SSL_VERIFYPEER => false
			);
			curl_setopt_array($ch, $options);
		}

		$response = curl_exec($ch);
		curl_close($ch);
		
		return $response;
	}
	
	
	/**
	* generateResponse
	*
	* This will generate the JSON response
	* @param :string,string/array
	* @return :  JSON
	*/
	public static function generateResponse($status,$msg)
	{
		$resp=new BaseResponse();			
		$resp->setStatus($status);		
		$resp->setData($msg);		
		return $resp;//error response
	}
	
	/**
	* generateErrResponse
	*
	* This will generate the JSON response
	* @param :string,string/array
	* @return :  JSON
	*/
	public static function generateErrResponse($status,$msg)
	{
		$resp=new BaseResponse();			
		$resp->setStatus($status);		
		$resp->setData(array("text" => $msg));		
		return $resp;//error response
	}

	/**
	 * Validates that all objects in the input array are non-empty
	 *
	 * Can be used as fields validation function inside package classes
	 *
	 */	
	public static function validateFilled($arrInputs) 
	{	
		foreach($arrInputs as $value)
		{
			if($value == '') 
			{
				return false;
			}
		}
		return true;
	}
	
	
	public static function randomAlphaNum($length) 
	{ 
		$random= "";
		srand((double)microtime()*1000000);

		$data = "AbCDE123IJKLMN67QRSTUVWXYZ"; 
		$data .= "ABCDEFGHIJKLMN123OPQ45RS67TUV89WXYZ"; 
		$data .= "0FGH45OP89";

		for($i = 0; $i < $length; $i++) 
		{ 
		$random .= substr($data, (rand()%(strlen($data))), 1); 
		}
		return $random; 
	}	

	
	}
?>
