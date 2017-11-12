<?php
namespace Awethemes\Http\Exception;

class ForbiddenException extends HttpException {
	/**
	 * Constructor.
	 *
	 * @param string     $message   The internal exception message.
	 * @param \Exception $previous  The previous exception.
	 * @param integer    $code      The internal exception code.
	 */
	public function __construct( $message = 'Forbidden', \Exception $previous = null, $code = 0 ) {
		parent::__construct( 403, $message, $previous, [], $code );
	}
}
