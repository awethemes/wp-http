<?php

use Awethemes\Http\WP_Error_Response;

class WP_Error_Response_Test extends WP_UnitTestCase {
	public function testConstructor() {
		$error = new WP_Error( 'error', 'Nothing' );
		$response = new WP_Error_Response( $error );

		$this->assertEquals(500, $response->status());
		$this->assertEquals('Nothing', $response->content());
		$this->assertEquals($error, $response->get_wp_error());
	}

	public function testConstructorWithCustomCode() {
		$response = new WP_Error_Response( new WP_Error( 'error', 'Not found' ), 404 );
		$this->assertEquals(404, $response->status());
	}

	public function testCustomCodeFromWPError() {
		$response = new WP_Error_Response( new WP_Error( 'error', 'Not found', [ 'status' => 404 ] ) );
		$this->assertEquals(404, $response->status());
	}
}
