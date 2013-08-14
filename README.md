AvaTax PHP API SDK
===========================

Simple PHP library for working with AvaTax's REST API

Requirements
------------

* curl
* PHP 5.3+
* AvaTax Developer account
    
Usage
-----

See below for a basic usage example. More examples can be found in the tests folder.
      
Express Checkout Example:

```
<?php

require_once 'avatax_php_sdk/AvaTax.php'; 
define("AVATAX_API_ACCOUNT_NUMBER", "your API account number");
define("AVATAX_API_LICENSE_KEY", "your API license key");
define("AVATAX_SANDBOX", true);

$tax = new AvaTaxCalc;

// Add origin and destination addresses
$tax->addAddress('132', '14 Test Street', 'Irvine', 'CA', '92632');
$tax->addAddress('133', '90 Test Lane', 'Los Angeles', 'CA', '90006');

// Add line items in order
$tax->addLine('556', '132', '133', 2, '99.90', 'SKU123', 'Golf Club');

$response = $tax->get("BRAND23", "2013-08-14");
if ($response->status == 'Success') $taxAmount = $response->TotalTax;

?>
