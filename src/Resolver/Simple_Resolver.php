<?php
namespace Awethemes\Http\Resolver;

use Symfony\Component\HttpFoundation\Request;

class Simple_Resolver implements Resolver {
	/**
	 * The incoming request.
	 *
	 * @var SymfonyRequest
	 */
	protected $request;

	/**
	 * Set the incoming request.
	 *
	 * @param Request $request The incoming request.
	 */
	public function imcomming_request( Request $request ) {
		$this->request = $request;
	}

	/**
	 * Call to the closure/callable action.
	 *
	 * @param  callable $action     The callable of the action.
	 * @param  array    $parameters The parameters for the action.
	 * @return mixed
	 */
	public function call( callable $action, array $parameters ) {
		return call_user_func_array( $action, array_merge( [ $this->request ], $parameters ) );
	}

	/**
	 * Call to the controller action.
	 *
	 * @param  string $controller The string of controller class.
	 * @param  array  $parameters The parameters for the action.
	 * @return mixed
	 */
	public function call_controller( $controller, array $parameters ) {
		if ( false !== strpos( $controller, '@' ) ) {
			$controller .= '@__invoke';
		}

		list( $class, $method ) = explode( '@', $controller );
		$instance = $this->resolve_controller_class( $class );

		return $this->call( [ $instance, $method ], $parameters );
	}

	/**
	 * Resolve controller class.
	 *
	 * @param  string $class The class name.
	 * @return mixed
	 */
	protected function resolve_controller_class( $class ) {
		return new $class;
	}
}
