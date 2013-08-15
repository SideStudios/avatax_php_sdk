<?php
/**
 * Sends requests to AvaTax
 *
 * @package    AvaTaxSDK
 * @subpackage AvaTaxRequest
 */
abstract class AvaTaxRequest {

	const LIVE_URL = 'https://avatax.avalara.net/1.0/';
	const SANDBOX_URL = 'https://development.avalara.net/1.0/';
	
	protected $_api_account;
	protected $_api_license_key;
	protected $_post_string; 
	public $VERIFY_PEER = false; // Set to false if getting connection errors.
	protected $_sandbox = true;
	protected $_log_file = false;
	
	/**
	 * Set the _post_string
	 */
	abstract protected function _setPostString();
	
	/**
	 * Handle the response string
	 */
	abstract protected function _handleResponse($string);
	
	/**
	 * Get the post url. We need this because until 5.3 you
	 * you could not access child constants in a parent class.
	 */
	abstract protected function _getPostUrl();
	
	/**
	 * Constructor.
	 *
	 * @param string $api_account      	API account number
	 * @param string $api_license_key	API license key
	 */
	public function __construct($api_account = false, $api_license_key = false)
	{
		$this->_api_account = ($api_account ? $api_account : (defined('AVATAX_API_ACCOUNT_NUMBER') ? AVATAX_API_ACCOUNT_NUMBER : ""));
		$this->_api_license_key = ($api_license_key ? $api_license_key : (defined('AVATAX_API_LICENSE_KEY') ? AVATAX_API_LICENSE_KEY : ""));
		$this->_sandbox = (defined('AVATAX_SANDBOX') ? AVATAX_SANDBOX : false);
		$this->_log_file = (defined('AVATAX_LOG_FILE') ? AVATAX_LOG_FILE : false);
	}

	/**
	 * Alter the gateway url.
	 *
	 * @param bool $bool Use the Sandbox.
	 */
	public function setSandbox($bool)
	{
		$this->_sandbox = $bool;
	}
	
	/**
	 * Set a log file.
	 *
	 * @param string $filepath Path to log file.
	 */
	public function setLogFile($filepath)
	{
		$this->_log_file = $filepath;
	}
	
	/**
	 * Return the post string.
	 *
	 * @return string
	 */
	public function getPostString()
	{
		return $this->_post_string;
	}
	
	/**
	 * Posts the request to AvaTax & returns response.
	 *
	 * @return AvaTaxResponse The response.
	 */
	protected function _sendRequest()
	{
		$this->_setPostString();
		$post_url = $this->_getPostUrl();
		$curl_request = curl_init($post_url);
		curl_setopt($curl_request, CURLOPT_USERPWD, $this->_api_account . ':' . $this->_api_license_key);
		curl_setopt($curl_request, CURLOPT_POSTFIELDS, $this->_post_string);
		curl_setopt($curl_request, CURLOPT_HEADER, false);
		curl_setopt($curl_request, CURLOPT_TIMEOUT, 45);
		curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_request, CURLOPT_SSL_VERIFYHOST, 2);
		if ($this->VERIFY_PEER) {
			curl_setopt($curl_request, CURLOPT_CAINFO, dirname(dirname(__FILE__)) . '/ssl/cert.pem');
		} else {
			curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, false);
		}
				
		$response = curl_exec($curl_request);
		
		if ($this->_log_file) {
		
			if ($curl_error = curl_error($curl_request)) {
				file_put_contents($this->_log_file, "----CURL ERROR----\n$curl_error\n\n", FILE_APPEND);
			}
			
			file_put_contents($this->_log_file, "----Response----\n$response\n\n", FILE_APPEND);
		}
		curl_close($curl_request);
		
		return $this->_handleResponse($response);
	}

}