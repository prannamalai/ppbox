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

require_once(__DIR__.'/core/util.class.php');
require_once(__DIR__.'/core/context.class.php');

/** 
 * Base controller of the framework. 
 *
 * This class is responsible for routing calls to appropriate module entry point classes 
 * based on multiple parameters.
 * 
 * Its designed keeping in mind the REST guidelines, and attempts to provide a way to refer dynamic resources.
 * Note: Only constructor and API functions to be made public.
 */
class BaseController {
	/**
	 * Constructor
	 */
	public function __construct() {
		Util::loadAllFilesFromDirectory(Context::coreClassesDirectory());
		Config::init(Context::iniFileName());
		$i18nDirectory = Context::i18nDirectory().'/'.Config::lookup('i18n', 'key').'/';
		Util::loadAllFilesFromDirectory($i18nDirectory);
	}
	
	/** 
	 * The maln router function.
	 *
	 * @param string $httpmethod HTTP method eg. GET or POST
	 * @param string $action action name eg. checkout
	 * @param string $methodAlias Method for the Action eg. credit_card 
	 * @param BaseReuqest $req Request data pertinent to action and method
	 *
	 * @return json Returns json which contains the response of API
	 */
	public function doAction($httpMethod, $action, $methodAlias, $req) {
		
		// Try to create the class object from action name using reflection
		try {
			$classname = $this->getClassNameFromActionName($action);
			$this->loadClass($classname);
			$refClass = new ReflectionClass($classname);
		} catch (ReflectionException $e) {
			// Class not found
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData('Undefined Action :'.$action);
		
			return $resp;
		}

		// Try to create the method object from method alias name using reflection
		try {
			$method = $this->getFunctionName($action, $methodAlias);

			// First, we'll try for soemthing like 'post_creditcard()'
			$refFunction = new ReflectionMethod($classname, $httpMethod.'_'.$method);
		} catch (ReflectionException $e) {
			// Method not found
			try {
				// Next, we'll try for somethinh like 'creditcard()'
				$refFunction = new ReflectionMethod($classname, $method);
			} catch (ReflectionException $e) {
				// Method not found
				// If both of above fail, falllback to _default() method
				$refFunction = new ReflectionMethod($classname, '_default');
			}
		}

		// Invoke the function 
		try {
			$obj = $refClass->newInstance();
			return $refFunction->invoke($obj, $req);
		} catch (Exception $e) {
			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData('Module Exception. Possible cause - incorrect path for log file or path not writable. Exact error message: '.$e->getMessage());
		
			return $resp;
		}

	}

	/** 
	 * Gets all supported actions by reading the configuration 
	 *
	 * This function is exposed for client applications also
	 */
	public function getActions() {
		$arrModules = Config::getSectionAsArray('actions');
		$data = array();

		foreach($arrModules as $key => $value) {
			$arrMethods = Config::getSectionAsArray($key);
			$arrMethodsNew = array();

			foreach($arrMethods as $key1 => $val1) {
				if(substr($val1, -10, 10) == '--disabled') {
					$arrMethods[$key1] = 'disabled';

					array_push($arrMethodsNew, array(
							'alias' => $key1,
							'enabled' => 'false')
						);
				} else {
					$arrMethods[$key1] = 'disabled';

					array_push($arrMethodsNew, array(
							'alias' => $key1,
							'enabled' => 'true')
						);
				}
			}

			array_push($data, array("module" => $key,
				"methods" => $arrMethodsNew
				));
		}

		if($arrModules) {
			$resp = new BaseResponse();
			$resp->setStatus('success');
			$resp->setData($data);

			return $resp;
		} else {

			$resp = new BaseResponse();
			$resp->setStatus('error');
			$resp->setData('No modules installed');

			return $resp;
		} 
	}
	
	/**
	 * Loads a class based on alias name 
	 */
	protected function loadClass($className) {
		if($className == null) {
			throw new ReflectionException();
			return;
		}
		$classFile = $this->getClassFileFromClassName($className);
		include_once($classFile);
	}
	
	/**
	 * Gets class file name from class name by doing a lookup in controller.ini
	 */
	protected function getClassFileFromClassName($className) {
		$classpath = Config::lookup('class_paths',$className);

		if($classpath != null) {
			return Context::moduleDirectory().$classpath;
		} else {
			return null;
		}
	}
	
	/**
	 * Gets actual class name from alias name by doing a lookup in controller.ini
	 */
	protected function getClassNameFromActionName($action) {
		return Config::lookup('actions',$action);
	}
	
	/**
	 * Gets actual function name from alias name by doing a lookup in controller.ini
	 */
	protected function getFunctionName($action, $methodAlias) {
		return (Config::lookup($action, $methodAlias) ? Config::lookup($action, $methodAlias) : $methodAlias);
	}
}

?>
