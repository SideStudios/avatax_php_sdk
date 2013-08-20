<?php

require_once 'AvaTax_Test_Config.php';

class AvaTaxCalc_Sandbox_Test extends PHPUnit_Framework_TestCase
{

	/**
	 * Test get tax call without addresses or lines
	 *
	 * Should return an error about addresses not being present
	 */
	 public function testGetTaxNoAddress() {

		$tax = new AvaTaxCalc;

		$response = $tax->get("CUST1", "2013-08-14");

		$this->assertEquals($response->status, 'Error');
		$this->assertContains('address', strtolower($response->errors[0]));

	}

	/**
	 * Test get tax call without addresses or lines
	 *
	 * Should output only an error about line items not being present
	 */
	 public function testGetTaxNoLines() {

		$tax = new AvaTaxCalc;
		$tax->addAddress('132', '14 Barranca Parkway', 'Irvine', 'CA', '92632');

		$response = $tax->get("CUST1", "2013-08-14");

		$this->assertEquals($response->status, 'Error');
		$this->assertContains('lines', strtolower($response->errors[0]));

	}

	/**
	 * Test get tax call for success
	 *
	 * Should return tax information correctly
	 */
	 public function testGetTax() {

		$tax = new AvaTaxCalc;
		$tax->addAddress('132', '14 Barranca Parkway', 'Irvine', 'CA', '92632');
		$tax->addAddress('133', '90 Azalea Drive', 'Yorktown', 'VA', '26362');
		$tax->addLine('556', '132', '133', 3, '33.33');

		$response = $tax->get("CUST1", "2013-08-14");

		$this->assertEquals($response->status, 'Success');
		$this->assertObjectHasAttribute('TotalTax', $response);

	}

	/**
	 * Test post tax call for success
	 *
	 * Should return tax information correctly and post to account
	 */
	 public function testPostTax($id = null) {

		$tax = new AvaTaxCalc;
		$tax->addAddress('132', '14 Barranca Parkway', 'Irvine', 'CA', '92632');
		$tax->addAddress('133', '90 Azalea Drive', 'Yorktown', 'VA', '26362');
		$tax->addLine('556', '132', '133', 3, '33.33');

		$response = $tax->post("CUST1", date("Y-m-d"), $id ? $id : rand(10000,90000));

		$this->assertEquals($response->status, 'Success');
		$this->assertObjectHasAttribute('TotalTax', $response);

	}

	/**
	 * Test cancel tax call with wrong document
	 *
	 * Should return error
	 */
	 public function testCancelTaxWrongDocument() {

		$tax = new AvaTaxCalc;
		
		$response = $tax->cancel("APITrialCompany", "111");

		$this->assertEquals($response->status, 'Error');
		$this->assertContains('document could not be found', strtolower($response->errors[0]));

	}

	/**
	 * Test cancel tax call 
	 *
	 * Should return success
	 */
	 public function testCancelTax() {

		$tax = new AvaTaxCalc;

		$r = rand(100, 200);

		$this->testPostTax($r);
		
		$response = $tax->cancel("APITrialCompany", $r);

		$this->assertEquals($response->status, 'Success');

	}

}