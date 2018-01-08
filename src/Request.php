<?php
namespace Awethemes\Http;

use Closure;
use ArrayAccess;
use RuntimeException;
use Awethemes\WP_Session\Session;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as Symfony_Request;

/**
 * The following methods are derived from code of the Laravel Framework
 * copyright (c) Taylor Otwell under MIT license.
 *
 * @see https://github.com/illuminate/http/blob/5.5/Request.php
 *
 * @license https://github.com/laravel/framework/blob/5.5/LICENSE.md
 */
class Request extends Symfony_Request implements Arrayable, ArrayAccess {
	use Traits\Request_With_Input,
		Traits\Request_With_Flash_Data,
		Traits\Request_With_Content_Types,
		Macroable;

	/**
	 * The decoded JSON content for the request.
	 *
	 * @var ParameterBag
	 */
	protected $json;

	/**
	 * The route resolver callback.
	 *
	 * @var \Closure
	 */
	protected $route_resolver;

	/**
	 * Create a new HTTP request from server variables.
	 *
	 * @return static
	 */
	public static function capture() {
		static::enableHttpMethodParameterOverride();

		return static::create_from_base( Symfony_Request::createFromGlobals() );
	}

	/**
	 * Return the Request instance.
	 *
	 * @return $this
	 */
	public function instance() {
		return $this;
	}

	/**
	 * Get the request method.
	 *
	 * @return string
	 */
	public function method() {
		return $this->getMethod();
	}

	/**
	 * Get the root URL for the application.
	 *
	 * @return string
	 */
	public function root() {
		return rtrim( $this->getSchemeAndHttpHost() . $this->getBaseUrl(), '/' );
	}

	/**
	 * Get the URL (no query string) for the request.
	 *
	 * @return string
	 */
	public function url() {
		return rtrim( preg_replace( '/\?.*/', '', $this->getUri() ), '/' );
	}

	/**
	 * Get the full URL for the request.
	 *
	 * @return string
	 */
	public function full_url() {
		$query = $this->getQueryString();

		$question = ( $this->getBaseUrl() . $this->getPathInfo() ) == '/' ? '/?' : '?';

		return $query ? ( $this->url() . $question . $query ) : $this->url();
	}

	/**
	 * Determine if the request is the result of an AJAX call.
	 *
	 * @return bool
	 */
	public function ajax() {
		return $this->isXmlHttpRequest();
	}

	/**
	 * Determine if the request is the result of an PJAX call.
	 *
	 * @return bool
	 */
	public function pjax() {
		return $this->headers->get( 'X-PJAX' ) == true;
	}

	/**
	 * Determine if the request is over HTTPS.
	 *
	 * @return bool
	 */
	public function secure() {
		return $this->isSecure();
	}

	/**
	 * Get the client IP address.
	 *
	 * @return string
	 */
	public function ip() {
		return $this->getClientIp();
	}

	/**
	 * Get the client IP addresses.
	 *
	 * @return array
	 */
	public function ips() {
		return $this->getClientIps();
	}

	/**
	 * Get the client user agent.
	 *
	 * @return string
	 */
	public function get_user_agent() {
		return $this->headers->get( 'User-Agent' );
	}

	/**
	 * Merge new input into the current request's input array.
	 *
	 * @param  array $input Added input parameters.
	 * @return void
	 */
	public function merge( array $input ) {
		$this->get_input_source()->add( $input );
	}

	/**
	 * Replace the input for the current request.
	 *
	 * @param  array $input Replace input parameters.
	 * @return void
	 */
	public function replace( array $input ) {
		$this->get_input_source()->replace( $input );
	}

	/**
	 * Get the JSON payload for the request.
	 *
	 * @param  string $key     Optional, special input key. If null ParameterBag will be return.
	 * @param  mixed  $default Default will be return if $key present and not found.
	 * @return ParameterBag|mixed
	 */
	public function json( $key = null, $default = null ) {
		if ( ! isset( $this->json ) ) {
			$this->json = new ParameterBag( (array) json_decode( $this->getContent(), true ) );
		}

		if ( is_null( $key ) ) {
			return $this->json;
		}

		return data_get( $this->json->all(), $key, $default );
	}

	/**
	 * Get the session associated with the request.
	 *
	 * @return \Awethemes\WP_Session\Session
	 * @throws RuntimeException
	 */
	public function session() {
		if ( ! $this->hasSession() ) {
			throw new RuntimeException( 'Session store not set on request.' );
		}

		return $this->getSession();
	}

	/**
	 * Get the route handling the request.
	 *
	 * @param  string|null $param Optional, get the special route parameter.
	 * @return array|string|null
	 */
	public function route( $param = null ) {
		$route = call_user_func( $this->get_route_resolver() );

		if ( is_null( $route ) || is_null( $param ) ) {
			return $route;
		}

		return array_key_exists( $param, $route[2] ) ? $route[2][ $param ] : null;
	}

	/**
	 * Get the current route pathinfo (e.g: /example).
	 *
	 * @return string
	 */
	public function route_path() {
		$current_route = $this->route();

		return isset( $current_route[3] ) ? $current_route[3] : null;
	}

	/**
	 * Get the route resolver callback.
	 *
	 * @return \Closure
	 */
	public function get_route_resolver() {
		return $this->route_resolver ?: function () {};
	}

	/**
	 * Set the route resolver callback.
	 *
	 * @param  \Closure $callback The callback.
	 * @return $this
	 */
	public function set_route_resolver( Closure $callback ) {
		$this->route_resolver = $callback;

		return $this;
	}

	/**
	 * Set the session instance on the request.
	 *
	 * @param  \Awethemes\WP_Session\Session $session The session store implementation.
	 * @return void
	 */
	public function set_wp_session( Session $session ) {
		$this->session = $session;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasPreviousSession() {
		return $this->hasSession() && $this->cookies->has( $this->session->get_name() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function duplicate( array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null ) {
		return parent::duplicate( $query, $request, $attributes, $cookies, $this->filter_files( $files ), $server );
	}

	/**
	 * Create an Request from a Symfony instance.
	 *
	 * @param  Symfony_Request $request The base request.
	 * @return Request
	 */
	public static function create_from_base( Symfony_Request $request ) {
		if ( $request instanceof static ) {
			return $request;
		}

		// Cache the base content, apply to clone later.
		$content = $request->content;

		$request = ( new static )->duplicate(
			$request->query->all(), $request->request->all(), $request->attributes->all(),
			$request->cookies->all(), $request->files->all(), $request->server->all()
		);

		$request->content = $content;
		$request->request = $request->get_input_source();

		return $request;
	}

	/**
	 * Filter the given array of files, removing any empty values.
	 *
	 * @param  mixed $files The input files.
	 * @return mixed
	 */
	protected function filter_files( $files ) {
		if ( ! $files ) {
			return;
		}

		foreach ( $files as $key => $file ) {
			if ( is_array( $file ) ) {
				$files[ $key ] = $this->filter_files( $files[ $key ] );
			}

			if ( empty( $files[ $key ] ) ) {
				unset( $files[ $key ] );
			}
		}

		return $files;
	}

	/**
	 * Get the input source for the request, just like $_REQUEST.
	 *
	 * @return \Symfony\Component\HttpFoundation\ParameterBag
	 */
	protected function get_input_source() {
		if ( $this->is_json() ) {
			return $this->json();
		}

		return $this->getRealMethod() == 'GET' ? $this->query : $this->request;
	}

	/**
	 * Alias of toArray() method.
	 *
	 * @return array
	 */
	public function to_array() {
		return $this->toArray();
	}

	/**
	 * Get all of the input and files for the request.
	 *
	 * @return array
	 */
	public function toArray() {
		return $this->all();
	}

	/**
	 * Determine if the given offset exists.
	 *
	 * @param  string $offset The offset to check.
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		$route_parameters = isset( $this->route()[2] ) ? $this->route()[2] : [];

		return array_key_exists( $offset, $this->all() + $route_parameters );
	}

	/**
	 * Get the value at the given offset.
	 *
	 * @param  string $offset The offset get.
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		return $this->__get( $offset );
	}

	/**
	 * Set the value at the given offset.
	 *
	 * @param  string $offset The setter offset.
	 * @param  mixed  $value  The setter value.
	 * @return void
	 */
	public function offsetSet( $offset, $value ) {
		$this->get_input_source()->set( $offset, $value );
	}

	/**
	 * Remove the value at the given offset.
	 *
	 * @param  string $offset The offset to unset.
	 * @return void
	 */
	public function offsetUnset( $offset ) {
		$this->get_input_source()->remove( $offset );
	}

	/**
	 * Check if an input element is set on the request.
	 *
	 * @param  string $key Isset key.
	 * @return bool
	 */
	public function __isset( $key ) {
		return ! is_null( $this->__get( $key ) );
	}

	/**
	 * Get an input element from the request.
	 *
	 * @param  string $key Getter key.
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( array_key_exists( $key, $this->all() ) ) {
			return data_get( $this->all(), $key );
		}

		return $this->route( $key );
	}
}
