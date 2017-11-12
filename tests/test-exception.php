<?php

use Awethemes\Http\Exception;

class Exception_Test extends \WP_UnitTestCase {
	/**
	 * Asserts that a HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\HttpException( 400, 'Bad Request', null, [ 'header' => 'value' ]);
		} catch ( \Exception $e ) {
			$this->assertSame( 400, $e->getStatusCode() );
			$this->assertSame( 'Bad Request', $e->getMessage() );
			$this->assertArrayHasKey( 'header', $e->getHeaders() );
		}
	}

	/**
	 * Asserts that a Bad Request HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testBadRequestHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\BadRequestException();
		} catch ( \Exception $e ) {
			$this->assertSame( 400, $e->getStatusCode() );
			$this->assertSame( 'Bad Request', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Conflict HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testConflictHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\ConflictException();
		} catch ( \Exception $e ) {
			$this->assertSame( 409, $e->getStatusCode() );
			$this->assertSame( 'Conflict', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Expectation Failed HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testExpectationFailedHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\ExpectationFailedException();
		} catch ( \Exception $e ) {
			$this->assertSame( 417, $e->getStatusCode() );
			$this->assertSame( 'Expectation Failed', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Forbidden HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testForbiddenHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\ForbiddenException();
		} catch ( \Exception $e ) {
			$this->assertSame( 403, $e->getStatusCode() );
			$this->assertSame( 'Forbidden', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Gone HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testGoneHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\GoneException();
		} catch ( \Exception $e ) {
			$this->assertSame( 410, $e->getStatusCode() );
			$this->assertSame( 'Gone', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Length Required HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testLengthRequiredHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\LengthRequiredException();
		} catch ( \Exception $e ) {
			$this->assertSame( 411, $e->getStatusCode() );
			$this->assertSame( 'Length Required', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Method Not Allowed HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testMethodNotAllowedHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\MethodNotAllowedException( [ 'GET', 'POST' ] );
		} catch ( \Exception $e ) {
			$this->assertSame( 405, $e->getStatusCode() );
			$this->assertSame( 'Method Not Allowed', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Not Acceptable HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testNotAcceptableHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\NotAcceptableException();
		} catch ( \Exception $e ) {
			$this->assertSame( 406, $e->getStatusCode() );
			$this->assertSame( 'Not Acceptable', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Not Found HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testNotFoundHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\NotFoundException();
		} catch ( \Exception $e ) {
			$this->assertSame( 404, $e->getStatusCode() );
			$this->assertSame( 'Not Found', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Precondition Failed HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testPreconditionFailedHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\PreconditionFailedException();
		} catch ( \Exception $e ) {
			$this->assertSame( 412, $e->getStatusCode() );
			$this->assertSame( 'Precondition Failed', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Precondition Required HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testPreconditionRequiredHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\PreconditionRequiredException();
		} catch ( \Exception $e ) {
			$this->assertSame( 428, $e->getStatusCode() );
			$this->assertSame( 'Precondition Required', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Too Many Requests HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testTooManyRequestsHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\TooManyRequestsException();
		} catch ( \Exception $e ) {
			$this->assertSame( 429, $e->getStatusCode() );
			$this->assertSame( 'Too Many Requests', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Unauthorized HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testUnauthorizedHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\UnauthorizedException();
		} catch ( \Exception $e ) {
			$this->assertSame( 401, $e->getStatusCode() );
			$this->assertSame( 'Unauthorized', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Unprocessable Entity HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testUnprocessableEntityHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\UnprocessableEntityException();
		} catch ( \Exception $e ) {
			$this->assertSame( 422, $e->getStatusCode() );
			$this->assertSame( 'Unprocessable Entity', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Unsupported Media HTTP Exception is built correctly when thrown
	 *
	 * @return void
	 */
	public function testUnsupportedMediaHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\UnsupportedMediaException();
		} catch ( \Exception $e ) {
			$this->assertSame( 415, $e->getStatusCode() );
			$this->assertSame( 'Unsupported Media', $e->getMessage() );
		}
	}

	/**
	 * Asserts that a Unavaliable For Legal Reasons HTTP Exception is built correctly when thrown.
	 *
	 * @return void
	 */
	public function testUnavailableForLegalReasonsHttpExceptionIsBuiltCorrectly() {
		try {
			throw new Exception\UnavailableForLegalReasonsException();
		} catch ( \Exception $e ) {
			$this->assertSame( 451, $e->getStatusCode() );
			$this->assertSame( 'Unavailable For Legal Reasons', $e->getMessage() );
		}
	}
}
