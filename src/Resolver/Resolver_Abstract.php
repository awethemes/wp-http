<?php
namespace Awethemes\Http\Resolver;

class Resolver_Abstract {
	/**
	 * TODO: ...
	 * Send the request through the pipeline with the given callback.
	 *
	 * @param  array  $middleware
	 * @param  \Closure  $then
	 * @return mixed
	 */
	protected function send_through_pipeline( array $middleware, Closure $then ) {
		if ( count( $middleware ) > 0 ) {
			return (new Pipeline)
				->send( $request )
				->through( $middleware )
				->then( $then );
		}

		return $then();
	}
}
