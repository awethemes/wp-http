<?php
namespace Awethemes\Http;

use ArrayObject;
use JsonSerializable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\HttpFoundation\Response as Symfony_Response;

class Response extends Symfony_Response {
	use Response_Trait;

	/**
	 * {@inheritdoc}
	 */
	public function send() {
		$this->sendHeaders();

		$this->sendContent();

		return $this;
	}

	/**
	 * Set the content on the response.
	 *
	 * Overwrite: \Symfony\Component\HttpFoundation\Response::setContent()
	 *
	 * @param  mixed $content Content that can be cast to string or JSONable.
	 * @return $this
	 */
	public function setContent( $content ) {
		$this->original = $content;

		if ( $this->should_be_json( $content ) ) {
			$this->header( 'Content-Type', 'application/json' );

			$content = $this->prepare_json_content( $content );
		}

		parent::setContent( $content );

		return $this;
	}

	/**
	 * Determine if the given content should be turned into JSON.
	 *
	 * @param  mixed $content The mixed content.
	 * @return bool
	 */
	protected function should_be_json( $content ) {
		return $content instanceof Jsonable ||
			   $content instanceof Arrayable ||
			   $content instanceof ArrayObject ||
			   $content instanceof JsonSerializable ||
			   is_array( $content );
	}

	/**
	 * Morph the given content into JSON.
	 *
	 * @param  mixed $content The mixed content.
	 * @return string
	 */
	protected function prepare_json_content( $content ) {
		if ( $content instanceof Jsonable ) {
			return $content->toJson();
		}

		if ( $content instanceof Arrayable ) {
			return json_encode( $content->toArray() );
		}

		return json_encode( $content );
	}
}
