<?php
namespace Awethemes\Http\Exception;

class HttpException extends \RuntimeException implements HttpExceptionInterface {
	/**
	 * HTTP status code.
	 *
	 * @var integer
	 */
	protected $status;

	/**
	 * HTTP response headers.
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Constructor.
	 *
	 * @param integer    $status   The valid HTTP status code.
	 * @param string     $message  The exception message.
	 * @param \Exception $previous The previous exception used for the exception chaining.
	 * @param array      $headers  An array of HTTP response headers.
	 * @param integer    $code     The Exception code.
	 */
	public function __construct( $status, $message = null, \Exception $previous = null, array $headers = [], $code = 0 ) {
		$this->status  = $status;
		$this->headers = $headers;

		parent::__construct( $message, $code, $previous );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStatusCode() {
		return $this->status;
	}

	/**
	 * Alias of getHeaders().
	 *
	 * @return array
	 */
	public function get_headers() {
		return $this->getHeaders();
	}

	/**
	 * Alias of getStatusCode().
	 *
	 * @return integer
	 */
	public function get_status_code() {
		return $this->getStatusCode();
	}
}
