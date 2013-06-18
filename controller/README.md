Base Controller
================
Base controller of the framework. This class is responsible for routing calls to appropriate module entry point classes 
based on multiple parameters.
Its designed keeping in mind the REST guidelines, and attempts to provide a way to refer dynamic resources.

This class has a single function 'doAction', which is supposed to handle all REST calls for all modules.

Function 'doAction'
------------------------
This function is a common controller function called by REST facade layer for all functionality. The job of this function is to extract the http method, the action name, the method alias and the request body and redirect to corresponding module based on the value of these parameters.

It reads config/controller.ini file to get the actual names of module classes and methods from action name and method alias respectively. In case any entries are not found in ini file, it attemps to call the class name and method name by using their aliases directly as a failover mechanism. If still the class/method is not found then it returns error.

Calling directly from PHP application
----------------------------------------
The function 'doAction' can be called directly from any PHP application instead of making a corresponding REST call.

	// Include required files. The relative paths may vary for your application
	require_once('controller/basecontroller.class.php');
	require_once('controller/core/baserequest.class.php');
	require_once('controller/core/baseresponse.class.php');

	$req = new BaseRequest($contentType);         // contentType can be 'json' or 'xml'
	$req->setDataFromString($requestBody);	      // The string request body in 'json' or 'xml' as specified by contentType
	// -- OR -- 	
	$req->setData($arrReq);                       // Alternatively, data in array format

	$base = new BaseController();
	$resp = $base->doAction($httpMethod, $action, $methodAlias, $req); // $action = Module name, $methodAlias = Method name

The $resp object you get back is an instance of `BaseResponse` class. Then you can use following methods to get data in json or xml format:
	
	print $resp->asJson();
	print $resp->asXml();

