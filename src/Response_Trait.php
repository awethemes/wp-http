<?php
namespace Awethemes\Http;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\HeaderBag;

trait Response_Trait {
	/**
	 * The original content of the response.
	 *
	 * @var mixed
	 */
	public $original;

	/**
	 * The exception that triggered the error response (if applicable).
	 *
	 * @var \Exception|null
	 */
	public $exception;

	/**
	 * Get the status code for the response.
	 *
	 * @return int
	 */
	public function status() {
		return $this->getStatusCode();
	}

	/**
	 * Get the content of the response.
	 *
	 * @return string
	 */
	public function content() {
		return $this->getContent();
	}

	/**
	 * Get the original response content.
	 *
	 * @return mixed
	 */
	public function get_original_content() {
		return $this->original;
	}

	/**
	 * Set a header on the Response.
	 *
	 * @param  string       $key     The header key.
	 * @param  array|string $values  The header value.
	 * @param  bool         $replace Replace existing header.
	 * @return $this
	 */
	public function header( $key, $values, $replace = true ) {
		$this->headers->set( $key, $values, $replace );

		return $this;
	}

	/**
	 * Add an array of headers to the response.
	 *
	 * @param  HeaderBag|array $headers An array of headers.
	 * @return $this
	 */
	public function with_headers( $headers ) {
		if ( $headers instanceof HeaderBag ) {
			$headers = $headers->all();
		}

		foreach ( $headers as $key => $value ) {
			$this->headers->set( $key, $value );
		}

		return $this;
	}

	/**
	 * Set the headers to prevent caching for the different browsers.
	 *
	 * @return $this
	 */
	public function no_cache() {
		nocache_headers();

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', 'true' );
		}

		if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
			define( 'DONOTCACHEOBJECT', 'true' );
		}

		if ( ! defined( 'DONOTCACHEDB' ) ) {
			define( 'DONOTCACHEDB', 'true' );
		}

		return $this;
	}

	/**
	 * Add a cookie to the response, alias of $this->with_cookie() method.
	 *
	 * @param  Cookie|mixed $cookie The Cookie instance or cookie name.
	 * @return $this
	 */
	public function cookie( $cookie ) {
		return call_user_func_array( [ $this, 'with_cookie' ], func_get_args() );
	}

	/**
	 * Add a cookie to the response.
	 *
	 * @see $this->create_cookie()
	 *
	 * @param  Cookie|mixed $cookie The Cookie instance or cookie name.
	 * @return $this
	 */
	public function with_cookie( $cookie ) {
		if ( is_string( $cookie ) ) {
			$cookie = call_user_func_array( [ $this, 'create_cookie' ], func_get_args() );
		}

		$this->headers->setCookie( $cookie );

		return $this;
	}

	/**
	 * Create a Cookie instance.
	 *
	 * @param string      $name     The name of the cookie.
	 * @param string|null $value    The value of the cookie.
	 * @param int         $minutes  The time the cookie expires.
	 * @param string      $path     The path on the server in which the cookie will be available on, default COOKIEPATH.
	 * @param string|null $domain   The domain that the cookie is available to, default COOKIE_DOMAIN.
	 * @param bool        $secure   Whether the cookie should only be transmitted over a secure HTTPS connection from the client.
	 * @param bool        $httponly Whether the cookie will be made accessible only through the HTTP protocol.
	 * @return Cookie
	 */
	public function create_cookie( $name, $value, $minutes = 0, $path = null, $domain = null, $secure = false, $httponly = true ) {
		$expire = ( 0 === $minutes ) ? 0 : ( time() + $minutes * MINUTE_IN_SECONDS );

		return new Cookie( $name, $value, $expire, $path ?: COOKIEPATH, $domain ?: COOKIE_DOMAIN, $secure, $httponly );
	}

	/**
	 * Set the exception to attach to the response.
	 *
	 * @param  \Exception $e The exception.
	 * @return $this
	 */
	public function with_exception( \Exception $e ) {
		$this->exception = $e;

		return $this;
	}
}
