<?php
use Cake\Routing\Router;

Router::plugin('SimpleExample', function ($routes) {
    $routes->fallbacks('InflectedRoute');
});
