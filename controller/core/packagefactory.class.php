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
 * Factory class to load package and create package class objects 
 */
class PackageFactory {

	/**
	 * Checks whether a given package has been defined in configuration ot not
	 */
	public static function isAvailable($pkg_alias) {
		if(Config::lookup('packages', $pkg_alias)) {
			//found valid entry in config file
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Loads a package
	 *
	 * @throws PackageNotFoundException
	 */
	public static function loadPackage($pkg_alias) {
		$pkgDirectory = Config::lookup('packages', $pkg_alias);
		if($pkgDirectory) {
			//found valid entry in config file
			Util::loadAllFilesFromDirectory(Context::packageDirectory().$pkgDirectory.'/');
		} else {
			throw new PackageNotFoundException($pkg_alias);
		}
	}

	/**
	 * Create object of package class
	 */
	public static function getObject($type_alias) {
		   // assumes the use of an autoloader
		   
		   $type = self::GetClassNameFromAlias($type_alias);
		   
		   if (class_exists($type)) {
			   return new $type();
		   }
		   else {
			   throw new ClassNotFoundException($type_alias);
		   }
	} 
	
	private static function GetClassNameFromAlias($alias) {
		// Will return package class from config file if found an entry. Otherwise it will return the alias name back
		// so that it acts as a failover for no entry in config file.
		return (Config::lookup('package_classes', $alias))?Config::lookup('package_classes', $alias):$alias;
	}
}

?>
