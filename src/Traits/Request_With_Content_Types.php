<?php
namespace Awethemes\Http\Traits;

trait Request_With_Content_Types {
	/**
	 * Determine if the request is sending JSON.
	 *
	 * @return bool
	 */
	public function is_json() {
		return $this->string_contains( $this->header( 'CONTENT_TYPE' ), [ '/json', '+json' ] );
	}

	/**
	 * Determine if the current request probably expects a JSON response.
	 *
	 * @return bool
	 */
	public function expects_json() {
		return ( $this->ajax() && ! $this->pjax() ) || $this->wants_json();
	}

	/**
	 * Determine if the current request is asking for JSON in return.
	 *
	 * @return bool
	 */
	public function wants_json() {
		$acceptable = $this->getAcceptableContentTypes();

		return isset( $acceptable[0] ) && $this->string_contains( $acceptable[0], [ '/json', '+json' ] );
	}

	/**
	 * Determine if a given string contains a given substring.
	 *
	 * @param  string       $haystack The given string.
	 * @param  string|array $needles  An array or string to check.
	 * @return bool
	 */
	protected function string_contains( $haystack, $needles ) {
		foreach ( (array) $needles as $needle ) {
			if ( '' !== $needle && mb_strpos( $haystack, $needle ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
