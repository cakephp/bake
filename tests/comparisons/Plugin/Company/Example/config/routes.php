<?php
use Cake\Routing\Router;

Router::plugin(
    'Company/Example',
    ['path' => '/company/example'],
    function ($routes) {
        $routes->fallbacks('DashedRoute');
    }
);
