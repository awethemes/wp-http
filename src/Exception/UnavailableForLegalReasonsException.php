<?php
namespace Awethemes\Http\Exception;

class UnavailableForLegalReasonsException extends HttpException {
	/**
	 * Constructor.
	 *
	 * @param string     $message   The internal exception message.
	 * @param \Exception $previous  The previous exception.
	 * @param integer    $code      The internal exception code.
	 */
	public function __construct( $message = 'Unavailable For Legal Reasons', \Exception $previous = null, $code = 0 ) {
		parent::__construct( 451, $message, $previous, [], $code );
	}
}
