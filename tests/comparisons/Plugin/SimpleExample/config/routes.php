<?php
declare(strict_types=1);

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/** @var \Cake\Routing\RouteBuilder $routes */
$routes->plugin(
    'SimpleExample',
    ['path' => '/simple-example'],
    function (RouteBuilder $builder) {
        $builder
            ->setRouteClass(DashedRoute::class)
            ->fallbacks();
    }
);
