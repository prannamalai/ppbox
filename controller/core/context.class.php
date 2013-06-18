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
 * Context class
 *
 * This class would provide context information to caller
 *
 * Useful for making application wide structural changes without much affecting the rest of the code
 * All members and methods are static, so callers would not need to create objects of this class
 *
 */ 
class Context {
	private static $baseControllerPath = 'ppbox/controller/';

	private static $moduleDirectory = 'modules/';
	private static $packageDirectory = 'packages/';
	private static $coreClassesDirectory = 'core/';
	private static $ini_file_name = 'config/controller.ini';
	private static $i18nDirectory = 'i18n/';

	public static function coreClassesDirectory() {
		return $_SERVER['DOCUMENT_ROOT'].'/'.self::$baseControllerPath.self::$coreClassesDirectory;
	}

	public static function iniFileName() {
		return $_SERVER['DOCUMENT_ROOT'].'/'.self::$baseControllerPath.self::$ini_file_name;
	}

	public static function moduleDirectory() {
		return $_SERVER['DOCUMENT_ROOT'].'/'.self::$baseControllerPath.self::$moduleDirectory;
	}

	public static function packageDirectory() {
		return $_SERVER['DOCUMENT_ROOT'].'/'.self::$baseControllerPath.self::$packageDirectory;
	}

	public static function i18nDirectory() {
		return $_SERVER['DOCUMENT_ROOT'].'/'.self::$baseControllerPath.self::$i18nDirectory;
	}
}
?>
