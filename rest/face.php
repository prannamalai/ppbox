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

require './vendor/autoload.php';

require_once('../controller/basecontroller.class.php');
require_once('../controller/core/baserequest.class.php');
require_once('../controller/core/baseresponse.class.php');

// We are using Slim framework for REST API.
// Note: Set 'debug' => true when debugging
$app = new \Slim\Slim(array(
	'debug' => false
));

/** 
 * Dynamically mapping the REST call for /{action_name}
 */
$base = new BaseController();
$resp = $base->getActions();
$resp = $resp->asArray();
foreach($resp['data'] as $module) {
	$app->get("/".$module['module'], 'get_handler');
	$app->post("/".$module['module'], 'post_handler');
	$app->put("/".$module['module'], 'put_handler');
}

/** 
 * Mapping of the REST GET call /actions
 * 
 * Returns back all available actions and method registered in configuration file
 * 
 * @http_method [get]
 */
$app->get('/actions', function() {
	
	global $app;

	$base = new BaseController();
	$resp = $base->getActions();

	$request = \Slim\Slim::getInstance()->request();
	$acceptType = $request->headers('Accept');
	if('json' == $acceptType) {
		$app->contentType('application/json');
		print $resp->asJson();

	} else if('xml' == $acceptType) {
		$app->contentType('application/xml');
		print $resp->asXml();

	} else { //default
		$app->contentType('application/json');
		print $resp->asJson();
	}
}
);

/** 
 * Function get_handler
 * 
 * Handles all HTTP GET requests for actions found in ../controller/config/controller.ini file
 * 
 * @http_method [get]
 */
function get_handler() {

	global $app;

	$request = \Slim\Slim::getInstance()->request();
	$action = ltrim($request->getPathInfo(), '/');
	$methodAlias = $request->headers('method');
	$contentType = $request->headers('Content-Type');
	$acceptType = $request->headers('Accept');
	$requestBody = $_GET;

	$req = new BaseRequest($contentType);
	$req->setData($requestBody);

	$base = new BaseController();
	$resp = $base->doAction('get', $action, $methodAlias, $req);

	if('json' == $acceptType) {
		$app->contentType('application/json');
		print $resp->asJson();

	} else if('xml' == $acceptType) {
		$app->contentType('application/xml');
		print $resp->asXml();

	} else { //default
		$app->contentType('application/json');
		print $resp->asJson();
	}
}

/** 
 * Function post_handler
 * 
 * Handles all HTTP POST requests for actions found in ../controller/config/controller.ini file
 * 
 * @http_method [post]
 */
function post_handler() {

	global $app;

	$request = \Slim\Slim::getInstance()->request();
	$action = ltrim($request->getPathInfo(), '/');
	$methodAlias = $request->headers('method');
	$contentType = $request->headers('Content-Type');
	$acceptType = $request->headers('Accept');
	$requestBody = $request->getBody();

	$req = new BaseRequest($contentType);
	$validInput = false;

	try {
		$req->setDataFromString($requestBody);
		$validInput = true;
	} catch(Exception $e) {
		$resp = new BaseResponse();
		$resp->setStatus('error');
		$resp->setData(array('text' => ERR_BAD_INPUT));
	}
	
	if(true == $validInput) {
		$base = new BaseController();
		$resp = $base->doAction('post', $action, $methodAlias, $req);
	}

	if('json' == $acceptType) {
		$app->contentType('application/json');
		print $resp->asJson();

	} else if('xml' == $acceptType) {
		$app->contentType('application/xml');
		print $resp->asXml();

	} else { //default
		$app->contentType('application/json');
		print $resp->asJson();
	}
}

/** 
 * Function put_handler
 * 
 * Handles all HTTP PUT requests for actions found in ../controller/config/controller.ini file
 * 
 * @http_method [put]
 */
function put_handler() {

	global $app;

	$request = \Slim\Slim::getInstance()->request();
	$action = ltrim($request->getPathInfo(), '/');
	$methodAlias = $request->headers('method');
	$contentType = $request->headers('Content-Type');
	$acceptType = $request->headers('Accept');
	$requestBody = $request->getBody();

	$req = new BaseRequest($contentType);
	$validInput = false;

	try {
		$req->setDataFromString($requestBody);
		$validInput = true;
	} catch(Exception $e) {
		$resp = new BaseResponse();
		$resp->setStatus('error');
		$resp->setData(array('text' => ERR_BAD_INPUT));
	}
	
	if(true == $validInput) {
		$base = new BaseController();
		$resp = $base->doAction('put', $action, $methodAlias, $req);
	}

	if('json' == $acceptType) {
		$app->contentType('application/json');
		print $resp->asJson();

	} else if('xml' == $acceptType) {
		$app->contentType('application/xml');
		print $resp->asXml();

	} else { //default
		$app->contentType('application/json');
		print $resp->asJson();
	}
}


$app->run();
?>
