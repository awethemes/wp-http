<?php
namespace Awethemes\Http\Traits;

use Illuminate\Support\Arr;

trait Request_With_Input {
	/**
	 * Retrieve a server variable from the request.
	 *
	 * @param  string $key     The server key.
	 * @param  mixed  $default The default value.
	 * @return string|array
	 */
	public function server( $key = null, $default = null ) {
		return $this->retrieve_item( 'server', $key, $default );
	}

	/**
	 * Determine if a header is set on the request.
	 *
	 * @param  string $key The header key to check.
	 * @return bool
	 */
	public function has_header( $key ) {
		return ! is_null( $this->header( $key ) );
	}

	/**
	 * Retrieve a header from the request.
	 *
	 * @param  string $key     The header key.
	 * @param  mixed  $default The default value.
	 * @return string|array
	 */
	public function header( $key = null, $default = null ) {
		return $this->retrieve_item( 'headers', $key, $default );
	}

	/**
	 * Get the bearer token from the request headers.
	 *
	 * @see https://tools.ietf.org/html/rfc6750#section-6.1.1
	 *
	 * @return string|null
	 */
	public function bearer_token() {
		$header = $this->header( 'Authorization', '' );

		if ( '' !== $header && 0 === substr( $header, 'Bearer ' ) ) {
			return substr( $header, 7 );
		}
	}

	/**
	 * Determine if the request contains a given input item key.
	 *
	 * @param  string|array $key An array keys or a string of special key.
	 * @return bool
	 */
	public function exists( $key ) {
		return $this->has( $key );
	}

	/**
	 * Determine if the request contains a given input item key.
	 *
	 * @param  string|array $key An array keys or a string of special key.
	 * @return bool
	 */
	public function has( $key ) {
		$keys = is_array( $key ) ? $key : func_get_args();

		$input = $this->all();

		foreach ( $keys as $value ) {
			if ( ! Arr::has( $input, $value ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determine if the request contains a non-empty value for an input item.
	 *
	 * @param  string|array $key An array keys or a string of special key.
	 * @return bool
	 */
	public function filled( $key ) {
		$keys = is_array( $key ) ? $key : func_get_args();

		foreach ( $keys as $value ) {
			if ( $this->is_empty_string( $value ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the keys for all of the input and files.
	 *
	 * @return array
	 */
	public function keys() {
		return array_merge( array_keys( $this->input() ), $this->files->keys() );
	}

	/**
	 * Get all of the input and files for the request.
	 *
	 * @param  array|mixed $keys Optional, get only keys.
	 * @return array
	 */
	public function all( $keys = null ) {
		$input = array_replace_recursive( $this->input(), $this->all_files() );

		if ( ! $keys ) {
			return $input;
		}

		$results = [];

		foreach ( is_array( $keys ) ? $keys : func_get_args() as $key ) {
			Arr::set( $results, $key, Arr::get( $input, $key ) );
		}

		return $results;
	}

	/**
	 * Retrieve an input item from the request.
	 *
	 * @param  string $key     The retrieve key.
	 * @param  mixed  $default The default value.
	 * @return string|array
	 */
	public function input( $key = null, $default = null ) {
		return data_get(
			$this->get_input_source()->all() + $this->query->all(), $key, $default
		);
	}

	/**
	 * Get a subset containing the provided keys with values from the input data.
	 *
	 * @param  array|mixed $keys The only keys.
	 * @return array
	 */
	public function only( $keys ) {
		$results = [];

		$input = $this->all();

		$placeholder = new \stdClass;

		foreach ( is_array( $keys ) ? $keys : func_get_args() as $key ) {
			$value = data_get( $input, $key, $placeholder );

			if ( $value !== $placeholder ) {
				Arr::set( $results, $key, $value );
			}
		}

		return $results;
	}

	/**
	 * Get all of the input except for a specified array of items.
	 *
	 * @param  array|mixed $keys The except keys.
	 * @return array
	 */
	public function except( $keys ) {
		$keys = is_array( $keys ) ? $keys : func_get_args();

		$results = $this->all();

		Arr::forget( $results, $keys );

		return $results;
	}

	/**
	 * Intersect an array of items with the input data.
	 *
	 * @param  array|mixed $keys The keys.
	 * @return array
	 */
	public function intersect( $keys ) {
		return array_filter( $this->only( is_array( $keys ) ? $keys : func_get_args() ) );
	}

	/**
	 * Retrieve a query string item from the request.
	 *
	 * @param  string $key     The retrieve key.
	 * @param  mixed  $default The default value.
	 * @return string|array
	 */
	public function query( $key = null, $default = null ) {
		return $this->retrieve_item( 'query', $key, $default );
	}

	/**
	 * Retrieve a request payload item from the request.
	 *
	 * @param  string            $key     The retrieve key.
	 * @param  string|array|null $default The default value.
	 * @return string|array
	 */
	public function post( $key = null, $default = null ) {
		return $this->retrieve_item( 'request', $key, $default );
	}

	/**
	 * Determine if a cookie is set on the request.
	 *
	 * @param  string $key The cookie key.
	 * @return bool
	 */
	public function has_cookie( $key ) {
		return ! is_null( $this->cookie( $key ) );
	}

	/**
	 * Retrieve a cookie from the request.
	 *
	 * @param  string $key     The retrieve key.
	 * @param  mixed  $default The default value.
	 * @return string|array
	 */
	public function cookie( $key = null, $default = null ) {
		return $this->retrieve_item( 'cookies', $key, $default );
	}

	/**
	 * Get an array of all of the files on the request.
	 *
	 * @return array
	 */
	public function all_files() {
		return $this->files->all();
	}

	/**
	 * Retrieve a file from the request.
	 *
	 * @param  string $key     The retrieve key.
	 * @param  mixed  $default The default value.
	 * @return \Symfony\Component\HttpFoundation\File\UploadedFile|array|null
	 */
	public function file( $key = null, $default = null ) {
		return data_get( $this->all_files(), $key, $default );
	}

	/**
	 * Determine if the uploaded data contains a file.
	 *
	 * @param  string $key The file key name to check.
	 * @return bool
	 */
	public function has_file( $key ) {
		$files = $this->file( $key );

		if ( ! is_array( $files ) ) {
			$files = [ $files ];
		}

		foreach ( $files as $file ) {
			if ( $this->is_valid_file( $file ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check that the given file is a valid file instance.
	 *
	 * @param  mixed $file The file to check.
	 * @return bool
	 */
	protected function is_valid_file( $file ) {
		return $file instanceof \SplFileInfo && $file->getPath() != '';
	}

	/**
	 * Determine if the given input key is an empty string for "has".
	 *
	 * @param  string $key A string key.
	 * @return bool
	 */
	protected function is_empty_string( $key ) {
		$value = $this->input( $key );

		return ! is_bool( $value ) && ! is_array( $value ) && trim( (string) $value ) === '';
	}

	/**
	 * Retrieve a parameter item from a given source.
	 *
	 * @param  string      $source  The retrieve source.
	 * @param  string|null $key     The key to retrieve.
	 * @param  mixed       $default The default value.
	 * @return string|array
	 */
	protected function retrieve_item( $source, $key, $default ) {
		if ( is_null( $key ) ) {
			return $this->$source->all();
		}

		return $this->$source->get( $key, $default );
	}
}
