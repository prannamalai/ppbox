Checkout module
======================
This module is responsible for handling all checkout/payment requests. It contains sub-modules for handling each different mode of checkout eg. Mobile checkout, VoicePay checkout etc.

class CheckOut 
--------------
This class represents the main checkout controller. It contains individual functions for handling different modes of checkout. 
For example, <br><br>
`public function post_credit_card($req)` <br><br>
is a HTTP post method handler for credit card type of checkout requests.

How to make calls to this module?
------------------------------------
For viewing sample REST calls specific to any module, navigate to its specific folder.
