<?php
use Cake\Routing\Router;

Router::plugin(
    'SimpleExample',
    ['path' => '/simple-example'],
    function ($routes) {
        $routes->fallbacks('DashedRoute');
    }
);
