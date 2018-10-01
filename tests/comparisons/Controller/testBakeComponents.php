<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * BakeArticles Controller
 *
 * @property \Cake\Controller\Component\RequestHandlerComponent $RequestHandler
 * @property \Cake\Controller\Component\AuthComponent $Auth
 * @property \Company\TestBakeThree\Controller\Component\SomethingComponent $Something
 * @property \TestBake\Controller\Component\OtherComponent $Other
 * @property \App\Controller\Component\AppleComponent $Apple
 * @property \App\Controller\Component\NonExistentComponent $NonExistent
 */
class BakeArticlesController extends AppController
{
    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Auth');
        $this->loadComponent('Company/TestBakeThree.Something');
        $this->loadComponent('TestBake.Other');
        $this->loadComponent('Apple');
        $this->loadComponent('NonExistent');
    }
}
