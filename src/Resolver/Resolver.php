<?php
namespace Awethemes\Http\Resolver;

interface Resolver {
	/**
	 * Call to the closure/callable action.
	 *
	 * @param  callable $action     The callable of the action.
	 * @param  array    $parameters The parameters for the action.
	 * @return mixed
	 */
	public function call( callable $action, array $parameters );

	/**
	 * Call to the controller action.
	 *
	 * @param  string $controller The string of controller class.
	 * @param  array  $parameters The parameters for the action.
	 * @return mixed
	 */
	public function call_controller( $controller, array $parameters );
}
