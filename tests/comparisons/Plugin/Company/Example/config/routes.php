<?php
declare(strict_types=1);

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

$routes->plugin(
    'Company/Example',
    ['path' => '/company/example'],
    function (RouteBuilder $scopedRoutes) {
        $scopedRoutes
            ->setRouteClass(DashedRoute::class)
            ->fallbacks();
    }
);
