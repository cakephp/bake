<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * BakeArticles Controller
 *
 * @property \Cake\Controller\Component\RequestHandlerComponent $RequestHandler
 * @property \Cake\Controller\Component\AuthComponent $Auth
 */
class BakeArticlesController extends AppController
{

    /**
     * Helpers
     *
     * @var array
     */
    public $helpers = ['Html', 'Time'];

    /**
     * Components
     *
     * @var array
     */
    public $components = ['RequestHandler', 'Auth'];
}
