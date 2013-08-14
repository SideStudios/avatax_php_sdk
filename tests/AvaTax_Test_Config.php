<?php
/**
 * Tests for the AvaTax PHP SDK
 */

/**
 * Enter your test account credentials to run tests against sandbox.
 */
define("AVATAX_API_ACCOUNT_NUMBER", "");
define("AVATAX_API_LICENSE_KEY", "");
define("AVATAX_SANDBOX", true);

define("AVATAX_LOG_FILE", dirname(__FILE__) . "/log");
// Clear logfile
file_put_contents(AVATAX_LOG_FILE, "");

if (!function_exists('curl_init')) {
    throw new Exception('The AvaTax SDK needs the CURL PHP extension.');
}

require_once dirname(dirname(__FILE__)) . '/AvaTax.php';

if (AVATAX_API_ACCOUNT_NUMBER == "") {
    die('Enter your AvaTax test credentials in '.__FILE__.' before running the test suite.');
}
