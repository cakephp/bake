<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::plugin(
    'Company/Example',
    ['path' => '/company/example'],
    function (RouteBuilder $routes) {
        $routes->fallbacks('DashedRoute');
    }
);
