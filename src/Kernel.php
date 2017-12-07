<?php
namespace Awethemes\Http;

use WP_Error;
use FastRoute\Dispatcher;
use Psr\Log\LoggerInterface;
use Awethemes\Http\Resolver\Resolver;
use Awethemes\Http\Resolver\Simple_Resolver;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler;

class Kernel {
	/**
	 * The resolver call.
	 *
	 * @var Resolver
	 */
	protected $resolver;

	/**
	 * The logger implementation.
	 *
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * Custom request_uri instead get from request.
	 *
	 * @var string
	 */
	protected $request_uri;

	/**
	 * The FastRoute Dispatcher instance.
	 *
	 * @var \FastRoute\Dispatcher
	 */
	protected $dispatcher;

	/**
	 * The current route being dispatched.
	 *
	 * @var array
	 */
	protected $current_route;

	/**
	 * The application's middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * Create a new HTTP kernel instance.
	 *
	 * @param Resolver $resolver The Resolver implementation.
	 */
	public function __construct( Resolver $resolver = null ) {
		$this->resolver = $resolver ?: new Simple_Resolver;
	}

	/**
	 * Get the Resolver.
	 *
	 * @return Resolver
	 */
	public function get_resolver() {
		return $this->resolver;
	}

	/**
	 * Set the Resolver.
	 *
	 * @param Resolver $resolver The Resolver implementation.
	 */
	public function set_resolver( Resolver $resolver ) {
		$this->resolver = $resolver;
	}

	/**
	 * Get the logger.
	 *
	 * @return LoggerInterface
	 */
	public function get_logger() {
		return $this->logger;
	}

	/**
	 * Set the logger.
	 *
	 * @param LoggerInterface $logger The logger implementation.
	 */
	public function set_logger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Uses custom request_uri instead get from request.
	 *
	 * @param  string $request_uri Custom request uri.
	 * @return $this
	 */
	public function use_request_uri( $request_uri ) {
		$this->request_uri = $request_uri;

		return $this;
	}

	/**
	 * Use custom dispatcher instead default.
	 *
	 * @param  Dispatcher $dispatcher The dispatcher instance.
	 * @return $this
	 */
	public function use_dispatcher( Dispatcher $dispatcher ) {
		$this->dispatcher = $dispatcher;

		return $this;
	}

	/**
	 * Handle the incoming request and process the response.
	 *
	 * @param  SymfonyRequest|null $request Optional, the Symfony Request instance.
	 * @return void
	 */
	public function handle( $request = null ) {
		$response = $this->dispatch( $request );

		if ( $response instanceof SymfonyResponse ) {
			$response->send();
		} else {
			echo (string) $response; // @WPCS XSS OK.
		}

		exit( 0 );
	}

	/**
	 * Dispatch the incoming request.
	 *
	 * @param  SymfonyRequest|null $request Optional, the Symfony Request instance.
	 * @return Response
	 */
	public function dispatch( $request = null ) {
		$request  = $this->resolve_request( $request );
		$pathinfo = '/' . trim( $this->request_uri ?: $request->getPathInfo(), '/' );

		try {
			$routeinfo = $this->get_dispatcher()
							  ->dispatch( $request->getMethod(), $pathinfo );

			return $this->handle_dispatcher( $request, $routeinfo );
		} catch ( \Exception $e ) {
			return $this->handler_exception( $e );
		} catch ( \Throwable $e ) {
			return $this->handler_exception( $e );
		}
	}

	/**
	 * Register the request routes, subclass must be implement this.
	 *
	 * @param \FastRoute\RouteCollector $route The route collector.
	 */
	protected function register_routes( $route ) {}

	/**
	 * Create a FastRoute dispatcher.
	 *
	 * @return \FastRoute\Dispatcher
	 */
	protected function get_dispatcher() {
		return $this->dispatcher ?: \FastRoute\simpleDispatcher( function ( $route ) {
			/**
			 * Use FastRoute syntax to register the route:
			 *
			 *      $route->get('/get-route', 'get_handler');
			 *      $route->post('/post-route', 'post_handler');
			 *      $route->addRoute('GET', '/do-something', 'function_handler');
			 *
			 * @see https://github.com/nikic/FastRoute#usage
			 */
			$this->register_routes( $route );
		});
	}

	/**
	 * Handle the response from the FastRoute dispatcher.
	 *
	 * @param  SymfonyRequest $request   The incoming request.
	 * @param  array          $routeinfo The response from dispatcher.
	 * @return Response
	 *
	 * @throws Exception\NotFoundException
	 * @throws Exception\MethodNotAllowedException
	 */
	protected function handle_dispatcher( SymfonyRequest $request, array $routeinfo ) {
		switch ( $routeinfo[0] ) {
			case Dispatcher::NOT_FOUND:
				throw new Exception\NotFoundException;
			case Dispatcher::METHOD_NOT_ALLOWED:
				throw new Exception\MethodNotAllowedException( (array) $routeinfo[1] );
			case Dispatcher::FOUND:
				return $this->handle_found_route( $request, $routeinfo );
		}
	}

	/**
	 * Handle a route found by the dispatcher.
	 *
	 * @param  SymfonyRequest $request   The incoming request.
	 * @param  array          $routeinfo The response from dispatcher.
	 * @return Response
	 */
	protected function handle_found_route( SymfonyRequest $request, array $routeinfo ) {
		$this->current_route = $routeinfo;

		if ( method_exists( $this->resolver, 'imcomming_request' ) ) {
			$this->resolver->imcomming_request( $request );
		}

		if ( method_exists( $request, 'set_route_resolver' ) ) {
			$request->set_route_resolver(function() {
				return $this->current_route;
			});
		}

		$action = $routeinfo[1];
		$parameters = method_exists( $request, 'route' ) ? $request->route()[2] : (array) $routeinfo[2];

		if ( is_string( $action ) ) {
			$response = $this->resolver->call_controller( $action, $parameters );
		} else {
			$response = $this->resolver->call( $action, $parameters );
		}

		return $this->prepare_response( $response );
	}

	/**
	 * Send the exception to the handler and return the response.
	 *
	 * @param  \Exception|\Throwable $e The Exception.
	 * @return Response
	 */
	protected function handler_exception( $e ) {
		// In PHP7+, throw a FatalThrowableError when we catch an Error.
		if ( $e instanceof \Error && class_exists( FatalThrowableError::class ) ) {
			$e = new FatalThrowableError( $e );
		}

		// Report the exception to the logger.
		if ( $this->get_logger() instanceof LoggerInterface ) {
			$this->get_logger()->error( $e->getMessage(), [ 'exception' => $e ] );
		}

		return $this->create_exception_response( $e );
	}

	/**
	 * Resolve the request.
	 *
	 * @param  mixed $request The request instance.
	 * @return Request
	 */
	protected function resolve_request( $request ) {
		if ( ! $request instanceof SymfonyRequest ) {
			$request = Request::capture();
		}

		return $request;
	}

	/**
	 * Prepare the response for sending.
	 *
	 * @param  mixed $response The responsable resources.
	 * @return Response
	 */
	protected function prepare_response( $response ) {
		if ( $response instanceof WP_Error ) {
			$response = new WP_Error_Response( $response );
		} elseif ( $response instanceof PsrResponseInterface ) {
			$response = ( new HttpFoundationFactory )->createResponse( $response );
		} elseif ( ! $response instanceof SymfonyResponse ) {
			$response = new Response( $response );
		}

		return $response;
	}

	/**
	 * Create a response of an Exception.
	 *
	 * @param  \Exception|\Throwable $e       The Exception.
	 * @param  integer               $status  The response status code.
	 * @param  array                 $headers The response headers.
	 * @return Response
	 *
	 * @throws mixed
	 */
	protected function create_exception_response( $e, $status = 500, array $headers = [] ) {
		if ( $e instanceof Exception\HttpExceptionInterface ) {
			$headers = $e->getHeaders();
			$status  = $e->getStatusCode();
		}

		// WP_DEBUG not enable, just response a simple error message.
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			$message = new WP_Error( 'http_error', $this->get_exception_message( $e, $status ) );
			return new WP_Error_Response( $message, $status, $headers );
		}

		// Response the exception via Symfony exception handler.
		if ( class_exists( ExceptionHandler::class ) ) {
			$fe = FlattenException::create( $e );
			$handler = new ExceptionHandler( true, get_bloginfo( 'charset' ) );

			$response = new Response( $handler->getHtml( $fe ), $status, $headers );
			if ( method_exists( $response, 'with_exception' ) ) {
				$response->with_exception( $e );
			}

			return $response;
		}

		// Throw given exception if any.
		throw $e;
	}

	/**
	 * Get the exception messages.
	 *
	 * @param  \Exception|\Throwable $e       The Exception.
	 * @param  integer               $status  The response status code.
	 * @return string
	 */
	protected function get_exception_message( $e, $status ) {
		switch ( $status ) {
			case 404:
				return 'Sorry, the page you are looking for could not be found.';
			default:
				return 'Whoops, looks like something went wrong.';
		}
	}
}
