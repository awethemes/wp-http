<?php
namespace Awethemes\Http\Traits;

trait Request_With_Flash_Data {
	/**
	 * Retrieve an old input item.
	 *
	 * @param  string            $key     The retrieve key.
	 * @param  string|array|null $default The default value.
	 * @return string|array
	 */
	public function old( $key = null, $default = null ) {
		return $this->session()->get_old_input( $key, $default );
	}

	/**
	 * Flash the input for the current request to the session.
	 *
	 * @return void
	 */
	public function flash() {
		$this->session()->flash_input( $this->input() );
	}

	/**
	 * Flash only some of the input to the session.
	 *
	 * @param  array|mixed $keys The keys.
	 * @return void
	 */
	public function flash_only( $keys ) {
		$this->session()->flash_input(
			$this->only( is_array( $keys ) ? $keys : func_get_args() )
		);
	}

	/**
	 * Flash only some of the input to the session.
	 *
	 * @param  array|mixed $keys The keys.
	 * @return void
	 */
	public function flash_except( $keys ) {
		$this->session()->flash_input(
			$this->except( is_array( $keys ) ? $keys : func_get_args() )
		);
	}

	/**
	 * Flush all of the old input from the session.
	 *
	 * @return void
	 */
	public function flush() {
		$this->session()->flash_input( [] );
	}
}
