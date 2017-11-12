<?php
namespace Awethemes\Http;

class WP_Error_Response extends Response {
	use Response_Trait;

	/**
	 * The WP_Error instance.
	 *
	 * @var WP_Error
	 */
	protected $wp_error;

	/**
	 * Create a WP_Error response.
	 *
	 * @param \WP_Error $wp_error The WP_Error instance.
	 * @param int       $status   The response status code, default is 500.
	 * @param array     $headers  An array of response headers.
	 *
	 * @throws \InvalidArgumentException When the HTTP status code is not valid.
	 */
	public function __construct( \WP_Error $wp_error, $status = 500, $headers = [] ) {
		$this->wp_error = $wp_error;

		$error_data = $wp_error->get_error_data();
		if ( is_array( $error_data ) && isset( $error_data['status'] ) ) {
			$status = $error_data['status'];
		}

		parent::__construct( $wp_error->get_error_message(), $status, $headers );
	}

	/**
	 * Get the WP_Error instance.
	 *
	 * @return WP_Error
	 */
	public function get_wp_error() {
		return $this->wp_error;
	}

	/**
	 * Sends content for the current web response.
	 *
	 * Overwrite parent::sendContent() method.
	 *
	 * @return void
	 */
	public function sendContent() {
		// @codingStandardsIgnoreLine
		wp_die( $this->wp_error, $this->statusCode );
	}
}
