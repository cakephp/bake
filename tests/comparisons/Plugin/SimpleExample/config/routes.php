<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::plugin(
    'SimpleExample',
    ['path' => '/simple-example'],
    function (RouteBuilder $routes) {
        $routes->fallbacks('DashedRoute');
    }
);
