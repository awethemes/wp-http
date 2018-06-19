<?php

use Mockery as m;
use Awethemes\Http\Kernel;
use Awethemes\Http\Request;
use Awethemes\Http\Response;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Kernel_Test extends WP_UnitTestCase {
	public function tearDown() {
		parent::tearDown();
		m::close();
	}

	public function testBasicRequest() {
		$kernel = new Kernel;

		$kernel->router(function ($routes) {
			$routes->get( '/', function () {
				return new Response( 'Hello World' );
			});
		});

		$response = $this->handleKernel( $kernel, Request::create( '/', 'GET' ) );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 'Hello World', $response->getContent() );
	}

	public function testBasicSymfonyRequest() {
		$kernel = new Kernel;

		$kernel->router( function ( $routes ) {
			$routes->get( '/', function () {
				return new Response( 'Hello World' );
			} );
		} );

		$response = $this->handleKernel( $kernel, SymfonyRequest::create( '/', 'GET' ) );
		$this->assertEquals( 200, $response->getStatusCode() );
	}

	public function testAddRouteMultipleMethodRequest() {
		$kernel = new Kernel;

		$kernel->router( function ( $routes ) {
			$routes->addRoute( [ 'GET', 'POST' ], '/', function () {
				return new Response( 'Hello World' );
			} );
		} );

		$response = $this->handleKernel( $kernel, Request::create( '/', 'GET' ) );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 'Hello World', $response->getContent() );

		$response = $this->handleKernel( $kernel, Request::create( '/', 'POST' ) );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 'Hello World', $response->getContent() );
	}

	public function testRequestWithParameters() {
		$kernel = new Kernel();

		$kernel->router( function ( $routes ) {
			$routes->get( '/foo/{bar}/{baz}', function ( $res, $bar, $baz ) {
				return new Response( $bar . $baz );
			} );
		} );

		$response = $this->handleKernel( $kernel, Request::create( '/foo/1/2', 'GET' ) );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( '12', $response->getContent() );
	}

	public function testCallbackRouteWithDefaultParameter() {
		$kernel = new Kernel();

		$kernel->router( function ( $routes ) {
			$routes->get( '/foo-bar/{baz}', function ( $res, $baz = 'default-value' ) {
				return new Response( $baz );
			});
		} );

		$response = $this->handleKernel( $kernel, Request::create( '/foo-bar/something', 'GET' ) );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 'something', $response->getContent() );
	}

	public function testGlobalMiddleware() {
		$app = new Kernel;
		$app->middleware( [ 'TestTerminateMiddleware' ] );

		$app->router(function ($routes) {
			$routes->get( '/', function () {
				return response( 'Hello World' );
			} );
		});

		$response = $this->handleKernel( $app, Request::create( '/', 'GET' ) );
		$this->assertEquals( 500, $response->getStatusCode() );
		$this->assertEquals( 'Middleware', $response->getContent() );
	}

	/**
	 * @return \Awethemes\Http\Response
	 */
	protected function handleKernel( Kernel $kernel, SymfonyRequest $request ) {
		$response = $kernel->dispatch( $request );

		return $response;
	}
}

function response( $content, $status = 200, $header = [] ) {
	return new Response( $content, $status, $header );
}

class TestTerminateMiddleware {
	public function handle( $request, $next ) {
		$response = $next( $request );

		$response->setStatusCode(500);
		$response->setContent( 'Middleware' );

		return $response;
	}
}
