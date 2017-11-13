<?php
namespace Awethemes\Http;

use JsonSerializable;
use InvalidArgumentException;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\HttpFoundation\JsonResponse as Symfony_Json_Response;

class Json_Response extends Symfony_Json_Response {
	use Response_Trait;

	/**
	 * Constructor.
	 *
	 * @param mixed $data    The response data.
	 * @param int   $status  The response status code.
	 * @param array $headers An array of response headers.
	 * @param int   $options  The options passed to json_encode() function.
	 */
	public function __construct( $data = null, $status = 200, $headers = [], $options = 0 ) {
		// @codingStandardsIgnoreLine
		$this->encodingOptions = $options;

		parent::__construct( $data, $status, $headers );
	}

	/**
	 * Get the json_decoded data from the response.
	 *
	 * @param  bool $assoc Return an associative arrays or not.
	 * @param  int  $depth User specified recursion depth.
	 * @return mixed
	 */
	public function getData( $assoc = false, $depth = 512 ) {
		return json_decode( $this->data, $assoc, $depth );
	}

	/**
	 * Sets the data to be sent as JSON.
	 *
	 * Overwrite \Symfony\Component\HttpFoundation\JsonResponse::setData()
	 *
	 * @param mixed $data The data to be send.
	 * @return $this
	 *
	 * @throws InvalidArgumentException
	 */
	public function setData( $data = [] ) {
		$this->original = $data;

		// @codingStandardsIgnoreLine
		$options = $this->encodingOptions;

		if ( $data instanceof Arrayable ) {
			$this->data = json_encode( $data->toArray(), $options );
		} elseif ( $data instanceof Jsonable ) {
			$this->data = $data->toJson( $options );
		} elseif ( $data instanceof JsonSerializable ) {
			$this->data = json_encode( $data->jsonSerialize(), $options );
		} else {
			$this->data = json_encode( $data, $options );
		}

		if ( ! $this->has_valid_json( json_last_error() ) ) {
			throw new InvalidArgumentException( json_last_error_msg() );
		}

		return $this->update();
	}

	/**
	 * Determine if an error occurred during JSON encoding.
	 *
	 * @param  int $json_error The json error code.
	 * @return bool
	 */
	protected function has_valid_json( $json_error ) {
		return JSON_ERROR_NONE === $json_error ||
			   ( JSON_ERROR_UNSUPPORTED_TYPE === $json_error && $this->has_encoding_option( JSON_PARTIAL_OUTPUT_ON_ERROR ) );
	}

	/**
	 * Determine if a JSON encoding option is set.
	 *
	 * @param  int $option JSON encoding option.
	 * @return bool
	 */
	protected function has_encoding_option( $option ) {
		// @codingStandardsIgnoreLine
		return (bool) ( $this->encodingOptions & $option );
	}
}
