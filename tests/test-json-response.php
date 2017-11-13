<?php

use Awethemes\Http\Json_Response;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class Json_Response_Test extends WP_UnitTestCase {

	public function testSetAndRetrieveJsonableData() {
		$response = new Json_Response( new JsonResponseTestJsonableObject() );
		$data = $response->getData();
		$this->assertInstanceOf( 'stdClass', $data );
		$this->assertEquals( 'bar', $data->foo );
	}

	public function testSetAndRetrieveJsonSerializeData() {
		$response = new Json_Response( new JsonResponseTestJsonSerializeObject() );
		$data = $response->getData();
		$this->assertInstanceOf( 'stdClass', $data );
		$this->assertEquals( 'bar', $data->foo );
	}

	public function testSetAndRetrieveArrayableData() {
		$response = new Json_Response( new JsonResponseTestArrayableObject() );
		$data = $response->getData();
		$this->assertInstanceOf( 'stdClass', $data );
		$this->assertEquals( 'bar', $data->foo );
	}

	public function testSetAndRetrieveData() {
		$response = new Json_Response( [ 'foo' => 'bar' ] );
		$data = $response->getData();
		$this->assertInstanceOf( 'stdClass', $data );
		$this->assertEquals( 'bar', $data->foo );
	}

	public function testGetOriginalContent() {
		$response = new Json_Response( new JsonResponseTestArrayableObject() );
		$this->assertInstanceOf( JsonResponseTestArrayableObject::class, $response->get_original_content() );

		$response = new Json_Response();
		$response->setData( new JsonResponseTestArrayableObject() );
		$this->assertInstanceOf( JsonResponseTestArrayableObject::class, $response->get_original_content() );
	}

	public function testSetAndRetrieveOptions() {
		$response = new Json_Response( [ 'foo' => 'bar' ] );
		$response->setEncodingOptions( JSON_PRETTY_PRINT );
		$this->assertSame( JSON_PRETTY_PRINT, $response->getEncodingOptions() );
	}

	public function testSetAndRetrieveDefaultOptions() {
		$response = new Json_Response( [ 'foo' => 'bar' ] );
		$this->assertSame( 0, $response->getEncodingOptions() );
	}

	public function testSetAndRetrieveStatusCode() {
		$response = new Json_Response( [ 'foo' => 'bar' ], 404 );
		$this->assertSame( 404, $response->getStatusCode() );

		$response = new Json_Response( [ 'foo' => 'bar' ] );
		$response->setStatusCode( 404 );
		$this->assertSame( 404, $response->getStatusCode() );
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Type is not supported
	 */
	public function testJsonErrorResource() {
		$resource = tmpfile();
		$response = new Json_Response( [ 'resource' => $resource ] );
	}

	public function testJsonErrorResourceWithPartialOutputOnError() {
		$resource = tmpfile();
		$response = new Json_Response( [ 'resource' => $resource ], 200, [], JSON_PARTIAL_OUTPUT_ON_ERROR );
		$data = $response->getData();
		$this->assertInstanceOf( 'stdClass', $data );
		$this->assertNull( $data->resource );
	}
}

class JsonResponseTestJsonableObject implements Jsonable {
	public function toJson( $options = 0 ) {
		return '{"foo":"bar"}';
	}
}

class JsonResponseTestJsonSerializeObject implements \JsonSerializable {
	public function jsonSerialize() {
		return [ 'foo' => 'bar' ];
	}
}

class JsonResponseTestArrayableObject implements Arrayable {
	public function toArray() {
		return [ 'foo' => 'bar' ];
	}
}
