<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * BakeArticles Controller
 *
 * @property \Cake\Controller\Component\CsrfComponent $Csrf
 * @property \Cake\Controller\Component\AuthComponent $Auth
 * @property \Company\TestBakeThree\Controller\Component\SomethingComponent $Something
 * @property \TestBake\Controller\Component\OtherComponent $Other
 * @property \App\Controller\Component\AppleComponent $Apple
 * @property \App\Controller\Component\NonExistentComponent $NonExistent
 */
final class BakeArticlesController extends AppController
{
    /**
     * Components
     *
     * @var array
     */
    public $components = ['Csrf', 'Auth', 'Company/TestBakeThree.Something', 'TestBake.Other', 'Apple', 'NonExistent'];
}
