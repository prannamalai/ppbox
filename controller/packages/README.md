Packages folder
=====================
This folder contains sub-directories specific to Paypal APIs eg. Payments Pro, Adaptive etc. Each of the packages should be registered in config/controller.ini for being able to be used inside modules.

Below is a list of available packages right now: <br>
* <b>Adaptive Payments</b> <br>
It encapsulates the functionality of Paypal's Adaptive Payments API to make simple payments using paypal registered email address
* <b>ClickPay</b> <br>
It is a simple package which generates URL from a few basic input parameters. It returns this URL back to caller. The caller can visit the URL to make payments to a single recipient
* <b>Payments Pro</b> <br>
It encapsulates the functionality of Paypal's Payments Pro API to make simple payments using credit card information
* <b>Share2pay</b> <br>
It contains the functionality to share payments among multiple payers and pay to a set of receivers


Adding new packages
-----------------------------------
1. Create a new folder for your package inside this directory
2. Create a class to represent your package. Refer to existing packages for examples.
3. If your package supports checkout functionality, implement "PayPalCheckout" interface.
4. Add alias of your package folder to ppbox/controller/config/controller.ini file under "packages" section.
5. Add entry of your package class to ppbox/controller/config/controller.ini file under "package_classes" section.

* Note: One package class should only focus on a single functionality of one PayPal API. And one package should only focus on one PayPal API group eg. PaymentsPro.

