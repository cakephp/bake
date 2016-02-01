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
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['BakeUsers']
        ];
        $bakeArticles = $this->paginate($this->BakeArticles);

        $this->set(compact('bakeArticles'));
        $this->set('_serialize', ['bakeArticles']);
    }

    /**
     * View method
     *
     * @param string|null $id Bake Article id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
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
