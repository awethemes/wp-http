<?php
namespace Awethemes\Http\Exception;

class MethodNotAllowedException extends HttpException {
	/**
	 * Constructor.
	 *
	 * @param array      $allowed   The method allowed.
	 * @param string     $message   The internal exception message.
	 * @param \Exception $previous  The previous exception.
	 * @param integer    $code      The internal exception code.
	 */
	public function __construct( array $allowed = [], $message = 'Method Not Allowed', \Exception $previous = null, $code = 0 ) {
		$headers = [ 'Allow' => implode( ', ', $allowed ) ];
		parent::__construct( 405, $message, $previous, $headers, $code );
	}
}
