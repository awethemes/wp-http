# WP-HTTP [![Build Status](https://travis-ci.org/awethemes/wp-http.svg?branch=master)](https://travis-ci.org/awethemes/wp-http)

Provide HTTP Request & Response for WordPress inspired by Laravel.

## Examples

```php
<?php

use Awethemes\Http\Kernel;
use Awethemes\Http\Request;

function my_plugin_register_routes( $router ) {
    $router->get( '/hello/{user}', function( Request $request, $user ) {
        return [ 'hello' => $user ];
    });
}

function my_plugin_dispatch() {
    global $wp;

    if ( empty( $wp->query_vars['my-route'] ) ) {
        return;
    }

    $kernel = new Kernel;
        ->use_request_uri( $wp->query_vars['my-route'] )
        ->use_dispatcher( \FastRoute\simpleDispatcher( 'my_plugin_register_routes' ) )
        ->handle( Request::capture() );
}
add_action( 'parse_request', 'my_plugin_dispatch' );
```

Test your route:

```
> curl -X GET "http://yoursite.dev/index.php?my-route=/hello/david"

{"hello":"david"}
```
