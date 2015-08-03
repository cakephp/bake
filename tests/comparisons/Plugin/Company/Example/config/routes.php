<?php
use Cake\Routing\Router;

Router::plugin('Company/Example', function ($routes) {
    $routes->fallbacks('DashedRoute');
});
