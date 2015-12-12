<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * BakeArticles Controller
 *
 * @property \App\Model\Table\BakeArticlesTable $BakeArticles
 * @property \Cake\Controller\Component\CsrfComponent $Csrf
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
    public $components = ['Csrf', 'Auth'];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['BakeUsers']
        ];
        $this->set('bakeArticles', $this->paginate($this->BakeArticles));
        $this->set('_serialize', ['bakeArticles']);
    }

    /**
     * View method
     *
     * @param string|null $id Bake Article id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $bakeArticle = $this->BakeArticles->get($id, [
            'contain' => ['BakeUsers', 'BakeTags', 'BakeComments']
        ]);
        $this->set('bakeArticle', $bakeArticle);
        $this->set('_serialize', ['bakeArticle']);
    }
}
