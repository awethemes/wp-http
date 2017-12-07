<?php
namespace Awethemes\Http;

use RuntimeException;
use Awethemes\WP_Session\Session;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse as Symfony_Redirect_Response;

class Redirect_Response extends Symfony_Redirect_Response {
	use Response_Trait,
		Macroable;

	/**
	 * The request instance.
	 *
	 * @var \Awethemes\Http\Request
	 */
	protected $request;

	/**
	 * The session store implementation.
	 *
	 * @var \Awethemes\WP_Session\Session
	 */
	protected $session;

	/**
	 * Creates a redirect response.
	 *
	 * @param string $url           The URL to redirect to.
	 * @param int    $status        The status code (302 by default).
	 * @param bool   $safe_redirect Use safe redirect or not.
	 * @param array  $headers       The headers (Location is always set to the given URL).
	 */
	public function __construct( $url, $status = 302, $safe_redirect = false, $headers = [] ) {
		$url = wp_sanitize_redirect( $url );

		$url = $safe_redirect ? $url : wp_validate_redirect( $url );

		parent::__construct( $url, $status, $headers );
	}

	/**
	 * Flash a piece of data to the session.
	 *
	 * @param  string|array $key   The flash key.
	 * @param  mixed        $value The flash value.
	 * @return $this
	 */
	public function with( $key, $value = null ) {
		$key = is_array( $key ) ? $key : [ $key => $value ];

		foreach ( $key as $k => $v ) {
			$this->get_session()->flash( $k, $v );
		}

		return $this;
	}

	/**
	 * Flash an array of input to the session.
	 *
	 * @param  array $input The input to send to next request.
	 * @return $this
	 */
	public function with_input( array $input = null ) {
		$this->get_session()->flash_input(
			$this->clean_input( ! is_null( $input ) ? $input : $this->get_request()->input() )
		);

		return $this;
	}

	/**
	 * Flash an array of input to the session.
	 *
	 * @return $this
	 */
	public function only_input() {
		return $this->with_input( $this->get_request()->only( func_get_args() ) );
	}

	/**
	 * Flash an array of input to the session.
	 *
	 * @return $this
	 */
	public function except_input() {
		return $this->with_input( $this->get_request()->except( func_get_args() ) );
	}

	/**
	 * Remove all uploaded files form the given input array.
	 *
	 * @param  array $input The input data.
	 * @return array
	 */
	protected function clean_input( array $input ) {
		foreach ( $input as $key => $value ) {
			if ( is_array( $value ) ) {
				$input[ $key ] = $this->clean_input( $value );
			}

			if ( $value instanceof UploadedFile ) {
				unset( $input[ $key ] );
			}
		}

		return $input;
	}

	/**
	 * Get the original response content.
	 *
	 * @return void
	 */
	public function get_original_content() {}

	/**
	 * Get the request instance.
	 *
	 * @return \Awethemes\Http\Request|null
	 *
	 * @throws RuntimeException
	 */
	public function get_request() {
		if ( ! $this->request ) {
			throw new RuntimeException( 'Request store not set on response.' );
		}

		return $this->request;
	}

	/**
	 * Set the request instance.
	 *
	 * @param  \Awethemes\Http\Request $request The request instance.
	 * @return void
	 */
	public function set_request( Request $request ) {
		$this->request = $request;
	}

	/**
	 * Get the session store implementation.
	 *
	 * @return \Awethemes\Session\Session|null
	 *
	 * @throws RuntimeException
	 */
	public function get_session() {
		if ( ! $this->session ) {
			throw new RuntimeException( 'Session store not set on response.' );
		}

		return $this->session;
	}

	/**
	 * Set the session store implementation.
	 *
	 * @param  \Awethemes\Session\Session $session The session store instance.
	 * @return void
	 */
	public function set_session( Session $session ) {
		$this->session = $session;
	}
}
