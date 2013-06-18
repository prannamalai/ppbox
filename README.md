Project Brief
==================================
ppbox is a framework to integrate various PayPal functionalities under a single library. It provides REST based interface to integrate with merchant's cart application. It also supports direct interface for PHP applications who want to avoid REST calls. 

The primary intent of this framework is to provide a common and simple interface for merchant's cart application along-with the support for configuring business functionalities and PayPal specific details separately (without affecting each other). We have designed this framework from ground-up for making it easy to add new functionality and PayPal API packages without touching the running functionality. Simplicity and configurability are our guiding principles.

Dependencies
==================================
* PHP 5.4.3
* curl extension for PHP

Getting Started - Local deployment
====================================
For deploying this project on your machine, you would need a web server capable of running PHP applications. We have tested this on Apache 2.2.22. You also need to configure Apache to run PHP. Once you are ready, please follow these steps:

1. Clone the repository
2. Deploy the ppbox folder in the document root of your web server
3. Configure path and (optionally) other log settings in controller/config/log4php_config.xml
4. Make your logs directory writable by running `chmod 777 full/path/to/your/logs/directory`
5. Start your web server

<b>Important Note:</b> Most of the functionality will not work unless `logs` folder is writable.

Now, the framework will be ready to accept REST calls. Please read documentation for the supported API and how to make calls.

In case you find any problems, do check the project wiki where we add any solutions we find to known problems related to deployment etc.

Documentation
==================================
More specific documentation can be found by navigating inside individual folders. But here's a quick index of most important pages: <br>
<table>
<tr>
	<td> 1. </td>
	<td width="500"> <a href = "https://raw.github.com/vaibhav276/ppbox/master/ComponentDiagram.png"> Component Diagram </a> </td>
</tr>
<tr>
	<td> 2. </td>
	<td> <a href = "https://github.com/vaibhav276/ppbox/tree/master/rest#rest-api"> The REST API </a> </td>
</tr>
<tr>
<tr>
	<td> 3. </td>
	<td> <a href = "https://github.com/vaibhav276/ppbox/tree/master/controller/config#config-folder"> Configuration </a> </td>
</tr>
<tr>
	<td> 4. </td>
	<td> <a href = "https://github.com/vaibhav276/ppbox/tree/master/controller/modules/checkout#checkout-module"> Checkout Module <a> </td>
</tr>
<tr>
	<td></td>
	<td>
	<ul>
		<li> <a href = "https://github.com/vaibhav276/ppbox/tree/master/controller/modules/checkout/creditcard#credit-card-checkout-module"> Credit Card </a> </li>
		<li> <a href = "https://github.com/vaibhav276/ppbox/tree/master/controller/modules/checkout/email#email-checkout-module"> Email </a></li>
		<li> <a href = "https://github.com/vaibhav276/ppbox/tree/master/controller/modules/checkout/share2pay#share2pay-checkout-module"> Share2Pay </a></li>
	</ul>
	</td>
</tr>
<tr>
	<td> 8. </td>
	<td> <a href = "https://github.com/vaibhav276/ppbox/tree/master/controller/modules/status#status-module"> Status Module <a> </td>
</tr>
<tr>
	<td> 9. </td>
	<td> <a href = "https://github.com/vaibhav276/ppbox/tree/master/controller/packages#packages-folder">Packages</a> </td>
</tr>
</table>

Terminology
====================================
This framework has two main types of components from developer perspective - modules and packages <br>
<b>Modules</b> - These represent business actions like checkout, checkin etc. <br>
<b>Packages</b> - These represent PayPal API packages like PaymentsPro, Adaptive etc. <br>

The intention behind this kind of separation is to provide flexibility for the user to configure (switch on/off) individual business functionalities without requiring the knowledge of PayPal APIs. On the other hand, to provide user the flexibility to configure individual PayPal API's (change end points, switch on/off) without modifying configuration of business functionalities.

Reference Implementation
===================================
We are working on providing a reference implementation application which would use this framework to process payments of a typical merchant's shopping cart. We will update it soon.

Contributing
===================================
1. Reporting bugs/ feature requests: <br>
Please report all bugs/feature requests to vaibhav276@yahoo.co.in

2. Adding new functionality: <br>
Please refer to below pages for adding new functionality to this framework: <br>
* <a href = "https://github.com/vaibhav276/ppbox/tree/master/controller/modules#adding-new-modules">Adding new modules</a> <br>
* <a href = "https://github.com/vaibhav276/ppbox/tree/master/controller/packages#adding-new-packages">Adding new packages</a> <br>


Disclaimer
===================================
We are using Apache 2.0 license for this project for providing the source code only and we do not assume any additional liability. Please be advised that since this framework is capable of handling personal and financial information of your customers, you need to comply with relevant guidelines. <em>Do your groundwork before deploying this in production</em>.
