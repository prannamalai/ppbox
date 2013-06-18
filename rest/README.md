REST API
===========
The REST API provides standard interface of the framework's functionality. Currently, we are having mapping for GET, POST and PUT methods for all modules registered in the controller config file. This mapping is generated dynamically to make adding/deleting modules without modifying anything in the REST layer. We are using Slim microframework as a base for our REST API.

How to use?
--------------
Below is a generic format for supported REST calls:

HTTP Methods: `GET`, `POST` and `PUT`. <br>
URL : `http://<hostname>/ppbox/rest/<module-name>`<br>
Headers: <br>
`method` = `<method-name>`<br>

Request Body: It currently supports  json/xml formats in body <br>
Response: It currently outputs data in json/xml formats 

Calling from PHP to avoid REST call
-----------------------------------------
If you have a PHP application, and you want to avoid going through REST interface you can directly invoke any flow. Please refer to <a href="https://github.com/vaibhav276/ppbox/tree/master/controller#calling-directly-from-php-application"> controller documentation </a> for this.

Get list of actions
----------------------
Apart from the above generic format for REST calls, there is one additional (fixed) call provided to 'get' list of all modules registered in config file. This call should be used to discover available modules in the framework.

HTTP Method: `GET`<br>
URL : `http://<hostname>/ppbox/rest/actions`<br>

Response:
<pre>
{
    "status": "success",
    "data": [
        {
            "module": "checkout",
            "methods": [
                {
                    "alias": "credit_card",
                    "enabled": true
                },                
                {
                    "alias": "email",
                    "enabled": true
                },
                {
                    "alias": "share2pay",
                    "enabled": true
                }
            ]
        },        
        {
            "module": "status",
            "methods": [
               {
                    "alias": "email",
                    "enabled": true
                }
            ]
        }
    ]
}

</pre>

The equivalent PHP call for calling directly would be:

	$base = new BaseController();
	$resp = $base->getActions();

