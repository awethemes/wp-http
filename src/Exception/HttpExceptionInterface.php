<?php
namespace Awethemes\Http\Exception;

interface HttpExceptionInterface {
	/**
	 * Return an array of headers provided when the exception was thrown.
	 *
	 * @return array
	 */
	public function getHeaders(); // @codingStandardsIgnoreLine

	/**
	 * Return the status code of the http exceptions.
	 *
	 * @return integer
	 */
	public function getStatusCode(); // @codingStandardsIgnoreLine
}
