<?php
/**
 * Easily interact with the AvaTax API.
 *
 * Note: To send requests to the live gateway, either define this:
 * define("AVATAX_SANDBOX", false);
 *   -- OR -- 
 * $sale = new AvaTax;
 * $sale->setSandbox(false);
 *
 * @package    AvaTax
 * @subpackage AvaTaxCalc
 */

/**
 * Builds and sends an AvaTax Request.
 *
 * @package    AvaTax
 * @subpackage AvaTaxClassic
 */
class AvaTaxCalc extends AvaTaxRequest {

	/**
	 * Holds all address data for the request
	 */
	protected $_addresses = array();
	
	/**
	 * Holds all line item data for the request
	 */
	protected $_lines = array();

	/**
	 * Holds all the default name/values that will be posted in the request. 
	 */
	protected $_post_fields = array();

	/**
	 * Resource to access in this request
	 * @var string
	 */
	protected $_resource;

	/**
	 * Checks to make sure a field is actually in the API before setting.
	 * Set to false to skip this check.
	 */
	public $verify_fields = true;
	
	/**
	 * A list of all string fields in the API.
	 * Used to warn user if they try to set a field not offered in the API.
	 */
	private $_string_fields = array("CancelCode","CustomerCode","DocDate","CompanyCode","Commit","CustomerUsageType",
		"Discount","DocCode","PurchaseOrderNo","ExemptionNo","DetailLevel","DocType",
		"ReferenceCode","PosLaneCode","Client","TaxOverride","BusinessIdentificationNo");
		  
	/**
	 * Alternative syntax for setting fields.
	 *
	 * Usage: $sale->method = "DoVoid";
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value) {
		$this->setField($name, $value);
	}

	/**
	 * Add an address for tax calculation
	 * @param string $AddressCode Address reference ID
	 * @param string $Line1       Address line
	 * @param string $City        Address city
	 * @param string $Region      State, province or region name
	 * @param string $PostalCode  Postal or ZIP code
	 * @param string $Country     ISO 3166-1 (Alpha-2) country code
	 * @param string $TaxRegionId AvaTax tax region identifier
	 */
	public function addAddress($AddressCode, $Line1, $City, $Region, $PostalCode, $Country = 'US', $TaxRegionId = null) {
		
		$this->_addresses[] = compact('AddressCode', 'Line1', 'City', 'Region', 'PostalCode', 'Country', 'TaxRegionId');

	}

	/**
	 * Add line for tax calculation
	 * @param string    $LineNo             Line item identifier
	 * @param string    $DestinationCode    Address code defined via addAddress() of destination address
	 * @param string    $OriginCode         Address code defined via addAddress() of origin address
	 * @param int       $Qty                Item quantity
	 * @param float     $Amount             Total amount of line (qty * unit price)
	 * @param string    $ItemCode           Item code (SKU, etc)
	 * @param string    $Description        Item description
	 * @param boolean   $Discounted         Is this item discounted? (set on document level)
	 * @param boolean   $TaxIncluded        Is tax already included on this item?
	 * @param string    $TaxCode            Tax code
	 * @param string    $CustomerUsageType  Entity/Use code
	 * @param string    $Ref1               Value stored with SalesInvoice DocType that is submitter dependent
	 * @param string    $Ref2               Value stored with SalesInvoice DocType that is submitter dependent
	 */
	public function addLine($LineNo, $DestinationCode, $OriginCode, $Qty, $Amount, $ItemCode = null, $Description = null, $Discounted = false, $TaxIncluded = false, $TaxCode = null, $CustomerUsageType = null, $Ref1 = null, $Ref2 = null) {
		
		$this->_lines[] = compact('LineNo', 'DestinationCode', 'OriginCode', 'Qty', 'Amount', 'ItemCode', 'Description', 'Discounted', 'TaxIncluded', 'TaxCode', 'CustomerUsageType', 'Ref1', 'Ref2');

	}

	/**
	 * Cancel tax post
	 * 
	 * @param  string $CompanyCode Client application customer reference code
	 * @param  string $DocCode     Invoice number, sales order number, etc
	 * @param  string $DocType     [SalesInvoice|ReturnInvoice|PurchaseInvoice] (defaults to SalesInvoice)
	 * @param  string $CancelCode  [Unspecified|PostFailed|DocDeleted|DocVoided|AdjustmentCancelled] (default to DocVoided)
	 * @return [type]              [description]
	 */
	public function cancel($CompanyCode = null, $DocCode = null, $DocType = null, $CancelCode = null) {

		($CompanyCode ? $this->CompanyCode = $CompanyCode : null);
		($DocCode ? $this->DocCode = $DocCode : null);
		$this->DocType = $DocType ? $DocType : 'SalesInvoice';
		$this->CancelCode = $CancelCode ? $CancelCode : 'DocVoided';

		$this->_resource = 'tax/cancel';

		return $this->_sendRequest();
	}

	/**
	 * Get tax information
	 *
	 * @param string $CustomerCode Client application customer reference code
	 * @param string $DocDate      Date of invoice, sales order, etc (YYYY-MM-DD)
	 * @return AvaTaxResponse      Response
	 */
	public function get($CustomerCode = null, $DocDate = null, $DocCode = null) {

		($CustomerCode ? $this->CustomerCode = $CustomerCode : null);
		($DocDate ? $this->DocDate = $DocDate : null);
		($DocCode ? $this->DocCode = $DocCode : null);
		
		$this->_resource = 'tax/get';

		return $this->_sendRequest();
	}

	/**
	 * Post tax information (like get(), but commits tax information to Avalara)
	 * 
	 * @see get() 
	 */
	public function post() {

		$this->Commit = true;
		$this->DocType = 'SalesInvoice';

		return call_user_func_array(array($this, 'get'), func_get_args());

	}

	/**
	 * Set an individual name/value pair.
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function setField($name, $value)
	{
		if ($this->verify_fields && !in_array($name, $this->_string_fields)) throw new AvaTaxException("Error: no field $name exists in the AvaTax API.");
		$this->_post_fields[$name] = $value;        
	}

	/**
	 * Quickly set multiple fields.
	 *
	 * @param array $fields Takes an array or object.
	 */
	public function setFields($fields)
	{
		$array = (array)$fields;
		foreach ($array as $key => $value) {
			$this->setField($key, $value);
		}
	}

	/**
	 * Unset a field.
	 *
	 * @param string $name Field to unset.
	 */
	public function unsetField($name)
	{
		unset($this->_post_fields[$name]);
	}

	/**
	 * @return string
	 */
	protected function _getPostUrl()
	{
		return ($this->_sandbox ? self::SANDBOX_URL : self::LIVE_URL) . $this->_resource . '/';
	}

	/**
	 * Handle a response
	 * @param string $response string
	 * @return AvaTaxResponse
	 */
	protected function _handleResponse($response)
	{
		if ($this->_resource == 'tax/cancel') return new AvaTaxCancel_Response($response);
		return new AvaTaxCalc_Response($response);
	}

	/**
	 * Converts the post_fields array into a string suitable for posting.
	 * @return void
	 */
	protected function _setPostString()
	{

		if ($this->_addresses) $this->_post_fields['Addresses'] = $this->_addresses;
		if ($this->_lines) $this->_post_fields['Lines'] = $this->_lines;

		$this->_post_string = json_encode($this->_post_fields);
	}

}

/**
 * Parses an AvaTax Calc Response
 *
 * @package    AvaTax
 * @subpackage AvaTaxCalc
 */
class AvaTaxCalc_Response extends AvaTaxResponse {

	public function __construct($response) {
		
		// Decode response
		if ($response) $this->_response_array = json_decode($response, true);
		parent::__construct($response);

	}

}

/**
 * Parses an AvaTax Cancel Response
 *
 * @package    AvaTax
 * @subpackage AvaTaxCalc
 */
class AvaTaxCancel_Response extends AvaTaxResponse
{

	public function __construct($response) {

		// Decode response
		if ($response) {
			$this->_response_array = json_decode($response, true);
			$this->_response_array = $this->_response_array['CancelTaxResult'];
		}
		
		parent::__construct($response);		

	}

}
