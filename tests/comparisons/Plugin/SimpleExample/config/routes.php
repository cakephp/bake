<?php
declare(strict_types=1);

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

$routes->plugin(
    'SimpleExample',
    ['path' => '/simple-example'],
    function (RouteBuilder $scopedRoutes) {
        $scopedRoutes
            ->setRouteClass(DashedRoute::class)
            ->fallbacks();
    }
);
