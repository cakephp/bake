<?php
namespace Bake\Test\App;

use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\RouteBuilder;

class Application extends BaseApplication {

    public function middleware(MiddlewareQueue $middleware): MiddlewareQueue
    {
        return $middleware;
    }

    public function routes(RouteBuilder $routes): void
    {
    }

    public function bootstrap(): void
    {
        $this->addPlugin('Bake');
    }
}
