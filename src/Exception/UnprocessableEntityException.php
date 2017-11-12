<?php
namespace Awethemes\Http\Exception;

class UnprocessableEntityException extends HttpException {
	/**
	 * Constructor.
	 *
	 * @param string     $message   The internal exception message.
	 * @param \Exception $previous  The previous exception.
	 * @param integer    $code      The internal exception code.
	 */
	public function __construct( $message = 'Unprocessable Entity', \Exception $previous = null, $code = 0 ) {
		parent::__construct( 422, $message, $previous, [], $code );
	}
}
