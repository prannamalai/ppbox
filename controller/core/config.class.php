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
 * Config helper class 
 */ 
final class Config {
	private static $ini_array;

	private function __construct() {}

	public static function init($ini_file_name) {
		self::$ini_array = parse_ini_file($ini_file_name, true);
	}

	/** 
	 * Lookup a value in cofiguration based on section and key
	 */ 
	public static function lookup($section, $key) {
		if(self::$ini_array == null) {
			return null;
		}

		if(!isset(self::$ini_array[$section][$key])) {
			return null;
		}

		return self::$ini_array[$section][$key];
	}

	/** 
	 * Get contents of section as an array 
	 */ 
	public static function getSectionAsArray($section) {
		return (isset(self::$ini_array[$section])?self::$ini_array[$section]:null);
	}
}

?>
