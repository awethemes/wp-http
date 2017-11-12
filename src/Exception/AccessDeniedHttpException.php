<?php
namespace Awethemes\Http\Exception;

class AccessDeniedHttpException extends ForbiddenException {
	/**
	 * Constructor.
	 *
	 * @param string     $message   The internal exception message.
	 * @param \Exception $previous  The previous exception.
	 * @param integer    $code      The internal exception code.
	 */
	public function __construct( $message = 'Access Denied', \Exception $previous = null, $code = 0 ) {
		parent::__construct( 403, $message, $previous, [], $code );
	}
}
