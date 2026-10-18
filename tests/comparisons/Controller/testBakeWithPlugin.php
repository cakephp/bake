<?php
declare(strict_types=1);

namespace BakeTest\Controller;

use Bake\Test\App\Controller\AppController;
use Cake\Http\Response;
use Cake\Routing\Attribute\Route;
use Cake\Routing\Attribute\Scope;

/**
 * BakeArticles Controller
 *
 * @property \BakeTest\Model\Table\BakeArticlesTable $BakeArticles
 */
#[Scope('/bake-test/bake-articles')]
class BakeArticlesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    #[Route('/')]
    #[Route('/index')]
    public function index()
    {
        $query = $this->BakeArticles->find()
            ->contain(['BakeUsers']);
        $bakeArticles = $this->paginate($query);

        $this->set(compact('bakeArticles'));
    }

    /**
     * View method
     *
     * @param string|null $id Bake Article id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    #[Route('/view/{id}')]
    public function view(mixed $id = null)
    {
        $bakeArticle = $this->BakeArticles->get($id, contain: ['BakeUsers', 'BakeTags', 'BakeComments']);
        $this->set(compact('bakeArticle'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    #[Route('/add')]
    public function add()
    {
        $bakeArticle = $this->BakeArticles->newEmptyEntity();
        if ($this->request->is('post')) {
            $bakeArticle = $this->BakeArticles->patchEntity($bakeArticle, $this->request->getData());
            if ($this->BakeArticles->save($bakeArticle)) {
                $this->Flash->success(__d('BakeTest', 'The bake article has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__d('BakeTest', 'The bake article could not be saved. Please, try again.'));
        }
        $bakeUsers = $this->BakeArticles->BakeUsers->find('list', limit: 200)->all();
        $bakeTags = $this->BakeArticles->BakeTags->find('list', limit: 200)->all();
        $this->set(compact('bakeArticle', 'bakeUsers', 'bakeTags'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Bake Article id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    #[Route('/edit/{id}')]
    public function edit(mixed $id = null)
    {
        $bakeArticle = $this->BakeArticles->get($id, contain: ['BakeTags']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $bakeArticle = $this->BakeArticles->patchEntity($bakeArticle, $this->request->getData());
            if ($this->BakeArticles->save($bakeArticle)) {
                $this->Flash->success(__d('BakeTest', 'The bake article has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__d('BakeTest', 'The bake article could not be saved. Please, try again.'));
        }
        $bakeUsers = $this->BakeArticles->BakeUsers->find('list', limit: 200)->all();
        $bakeTags = $this->BakeArticles->BakeTags->find('list', limit: 200)->all();
        $this->set(compact('bakeArticle', 'bakeUsers', 'bakeTags'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Bake Article id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    #[Route('/delete/{id}')]
    public function delete(mixed $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $bakeArticle = $this->BakeArticles->get($id);
        if ($this->BakeArticles->delete($bakeArticle)) {
            $this->Flash->success(__d('BakeTest', 'The bake article has been deleted.'));
        } else {
            $this->Flash->error(__d('BakeTest', 'The bake article could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
