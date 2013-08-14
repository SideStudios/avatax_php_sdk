<?php
/**
 * Base class for the AvaTax responses.
 *
 * @package    AvaTax
 * @subpackage    AvaTaxResponse
 */


/**
 * Parses an AvaTax Response.
 *
 * @package AvaTax
 * @subpackage    AvaTaxResponse
 */
class AvaTaxResponse
{

    public $status;
    public $errors = array();
    public $warnings = array();
    public $response; // The raw response from AvaTax.
    public $_response_array = array(); // An array with the split response.


	/**
	 * Constructor. Parses generic fields in AvaTax response
	 *
	 * @param string $response      The response from the AvaTax server.
	 */
	public function __construct($response) {
		
		$this->response = $response;
		
		if ($this->_response_array) {

			// Set response fields
			foreach($this->_response_array as $key => $value) {
				if ($key == 'ResultCode') $this->status = $value;
				else if ($key == 'Messages') {
					foreach($value as $message) {
						if ($message['Severity'] == 'Error') $this->errors[] = $message['Summary'];
						else $this->warnings[] = $message['Summary'];
					}
				}
				else $this->{$key} = $value;
			}
			
		} else {
			$this->status = 'Error';
			$this->errors[] = 'Error connecting to AvaTax';
		}

	}

}
