<?php

use Mockery as m;
use Awethemes\Http\Request;
use Awethemes\Http\Redirect_Response;
use Symfony\Component\HttpFoundation\Cookie;

class Redirect_Response_Test extends WP_UnitTestCase {
	public function tearDown() {
		parent::tearDown();
		m::close();
	}

	public function testHeaderOnRedirect() {
		$response = new Redirect_Response('foo.bar');
		$this->assertNull($response->headers->get('foo'));
		$response->header('foo', 'bar');
		$this->assertEquals('bar', $response->headers->get('foo'));
		$response->header('foo', 'baz', false);
		$this->assertEquals('bar', $response->headers->get('foo'));
		$response->header('foo', 'baz');
		$this->assertEquals('baz', $response->headers->get('foo'));
	}

	public function testWithOnRedirect() {
		$response = new Redirect_Response('foo.bar');
		$response->set_request(Request::create('/', 'GET', ['name' => 'Taylor', 'age' => 26]));
		$response->set_session($session = m::mock('Awethemes\WP_Session\Store'));
		$session->shouldReceive('flash')->twice();
		$response->with(['name', 'age']);
	}

	public function testWithCookieOnRedirect() {
		$response = new Redirect_Response('foo.bar');
		$this->assertCount(0, $response->headers->getCookies());

		$this->assertEquals($response, $response->with_cookie(new Cookie('foo', 'bar')));
		$cookies = $response->headers->getCookies();

		$this->assertCount(1, $cookies);
		$this->assertEquals('foo', $cookies[0]->getName());
		$this->assertEquals('bar', $cookies[0]->getValue());
	}

	public function testOnlyInputOnRedirect() {
		$response = new Redirect_Response('foo.bar');
		$response->set_request(Request::create('/', 'GET', ['name' => 'Taylor', 'age' => 26]));

		$response->set_session($session = m::mock('Awethemes\WP_Session\Store'));
		$session->shouldReceive('flash_input')->once()->with(['name' => 'Taylor']);

		$response->only_input('name');
	}

	public function testExceptInputOnRedirect() {
		$response = new Redirect_Response('foo.bar');
		$response->set_request(Request::create('/', 'GET', ['name' => 'Taylor', 'age' => 26]));

		$response->set_session($session = m::mock('Awethemes\WP_Session\Store'));
		$session->shouldReceive('flash_input')->once()->with(['name' => 'Taylor']);

		$response->except_input('age');
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testExceptionWhenNotSetSessionAndRequest() {
		$response = new Redirect_Response('foo.bar');

		$response->get_session();
		$response->get_request();
	}

	public function testSettersGettersOnRequest() {
		$response = new Redirect_Response('foo.bar');

		$request = Request::create('/', 'GET');
		$session = m::mock('Awethemes\WP_Session\Store');

		$response->set_request($request);
		$response->set_session($session);

		$this->assertSame($request, $response->get_request());
		$this->assertSame($session, $response->get_session());
	}
}
