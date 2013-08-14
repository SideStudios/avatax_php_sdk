<?php
/**
 * The AvaTax PHP SDK. Include this file in your project.
 *
 * @package AvaTax
 */
require dirname(__FILE__) . '/lib/shared/AvaTaxRequest.php';
require dirname(__FILE__) . '/lib/shared/AvaTaxResponse.php';
require dirname(__FILE__) . '/lib/AvaTaxCalc.php';

/**
 * Exception class for AvaTax PHP SDK.
 *
 * @package AvaTax
 */
class AvaTaxException extends Exception
{
}