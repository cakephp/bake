<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * BakeArticles Controller
 *
 * @property \Cake\Controller\Component\CsrfComponent $Csrf
 * @property \Cake\Controller\Component\AuthComponent $Auth
 */
final class BakeArticlesController extends AppController
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
    public $components = ['Csrf', 'Auth'];
}
