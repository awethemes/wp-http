<?php

use Mockery as m;
use Awethemes\Http\Request;
use Awethemes\Http\Response;
use Awethemes\Http\Redirect_Response;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\HttpFoundation\Cookie;

class Response_Test extends WP_UnitTestCase {
	public function tearDown() {
		parent::tearDown();
		m::close();
	}

	public function testJsonResponsesAreConvertedAndHeadersAreSet() {
		$response = new Response( new ArrayableStub );
		$this->assertEquals( '{"foo":"bar"}', $response->getContent() );
		$this->assertEquals( 'application/json', $response->headers->get( 'Content-Type' ) );

		$response = new Response( new JsonableStub );
		$this->assertEquals( 'foo', $response->getContent() );
		$this->assertEquals( 'application/json', $response->headers->get( 'Content-Type' ) );

		$response = new Response( new ArrayableAndJsonableStub );
		$this->assertEquals( '{"foo":"bar"}', $response->getContent() );
		$this->assertEquals( 'application/json', $response->headers->get( 'Content-Type' ) );

		$response = new Response();
		$response->setContent( [ 'foo' => 'bar' ] );
		$this->assertEquals( '{"foo":"bar"}', $response->getContent() );
		$this->assertEquals( 'application/json', $response->headers->get( 'Content-Type' ) );

		$response = new Response( new JsonSerializableStub );
		$this->assertEquals( '{"foo":"bar"}', $response->getContent() );
		$this->assertEquals( 'application/json', $response->headers->get( 'Content-Type' ) );

		$response = new Response( new ArrayableStub );
		$this->assertEquals( '{"foo":"bar"}', $response->getContent() );
		$this->assertEquals( 'application/json', $response->headers->get( 'Content-Type' ) );
		$response->setContent( '{"foo": "bar"}' );
		$this->assertEquals( '{"foo": "bar"}', $response->getContent() );
		$this->assertEquals( 'application/json', $response->headers->get( 'Content-Type' ) );
	}

	public function testAliasStatusAndContent() {
		$response = new Response('Nooo, Not found!', 404);

		$this->assertEquals( 404, $response->status() );
		$this->assertEquals( 'Nooo, Not found!', $response->content() );

		$this->assertEquals( $response->content(), $response->getContent() );
		$this->assertEquals( $response->status(), $response->getStatusCode() );
	}

	public function testHeader() {
		$response = new Response;
		$this->assertNull($response->headers->get('foo'));
		$response->header('foo', 'bar');
		$this->assertEquals('bar', $response->headers->get('foo'));
		$response->header('foo', 'baz', false);
		$this->assertEquals('bar', $response->headers->get('foo'));
		$response->header('foo', 'baz');
		$this->assertEquals('baz', $response->headers->get('foo'));
	}

	public function testWithHeaders() {
		$response = new Response(null, 200, ['foo' => 'bar']);
		$this->assertSame('bar', $response->headers->get('foo'));

		$response->with_headers(['foo' => 'BAR', 'bar' => 'baz']);
		$this->assertSame('BAR', $response->headers->get('foo'));
		$this->assertSame('baz', $response->headers->get('bar'));

		$responseMessageBag = new \Symfony\Component\HttpFoundation\ResponseHeaderBag(['bar' => 'BAZ', 'titi' => 'toto']);
		$response->with_headers($responseMessageBag);
		$this->assertSame('BAZ', $response->headers->get('bar'));
		$this->assertSame('toto', $response->headers->get('titi'));

		$headerBag = new \Symfony\Component\HttpFoundation\HeaderBag(['bar' => 'BAAA', 'titi' => 'TATA']);
		$response->with_headers($headerBag);
		$this->assertSame('BAAA', $response->headers->get('bar'));
		$this->assertSame('TATA', $response->headers->get('titi'));
	}

	public function testCreateCookie() {
		$response = new Response;
		$cookie = $response->create_cookie( 'foo', 'bar', 24, '/path/to/', 'sub.domain.com', true, false );

		$this->assertEquals('foo', $cookie->getName());
		$this->assertEquals('bar', $cookie->getValue());
		$this->assertEquals('sub.domain.com', $cookie->getDomain());
		$this->assertEquals('/path/to/', $cookie->getPath());
		$this->assertTrue($cookie->isSecure());
		$this->assertFalse($cookie->isHttpOnly());
	}

	public function testCookie() {
		$response = new Response;
		$this->assertCount(0, $response->headers->getCookies());
		$this->assertEquals($response, $response->cookie('foo', 'bar'));

		$cookies = $response->headers->getCookies();
		$this->assertCount(1, $cookies);
		$this->assertEquals('foo', $cookies[0]->getName());
		$this->assertEquals('bar', $cookies[0]->getValue());
	}

	public function testWithCookie() {
		$response = new Response;
		$this->assertCount(0, $response->headers->getCookies());
		$this->assertEquals($response, $response->with_cookie(new Cookie('foo', 'bar')));

		$cookies = $response->headers->getCookies();
		$this->assertCount(1, $cookies);
		$this->assertEquals('foo', $cookies[0]->getName());
		$this->assertEquals('bar', $cookies[0]->getValue());
	}

	public function testGetOriginalContent() {
		$arr = ['foo' => 'bar'];
		$response = new Response;
		$response->setContent($arr);

		$this->assertSame($arr, $response->get_original_content());
	}

	public function testSetAndRetrieveStatusCode() {
		$response = new Response('foo');
		$response->setStatusCode(404);

		$this->assertSame(404, $response->status());
		$this->assertSame(404, $response->getStatusCode());
	}
}

class ArrayableStub implements Arrayable {
	public function toArray() {
		return [ 'foo' => 'bar' ];
	}
}

class ArrayableAndJsonableStub implements Arrayable, Jsonable {
	public function toJson( $options = 0 ) {
		return '{"foo":"bar"}';
	}

	public function toArray() {
		return [];
	}
}

class JsonableStub implements Jsonable {
	public function toJson( $options = 0 ) {
		return 'foo';
	}
}

class JsonSerializableStub implements JsonSerializable {
	public function jsonSerialize() {
		return [ 'foo' => 'bar' ];
	}
}
