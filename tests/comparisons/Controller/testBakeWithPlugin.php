<?php
namespace BakeTest\Controller;

use BakeTest\Controller\AppController;

/**
 * BakeArticles Controller
 *
 * @property \BakeTest\Model\Table\BakeArticlesTable $BakeArticles
 */
class BakeArticlesController extends AppController
{

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

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $bakeArticle = $this->BakeArticles->newEntity();
        if ($this->request->is('post')) {
            $bakeArticle = $this->BakeArticles->patchEntity($bakeArticle, $this->request->data);
            if ($this->BakeArticles->save($bakeArticle)) {
                $this->Flash->success(__('The bake article has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The bake article could not be saved. Please, try again.'));
            }
        }
        $bakeUsers = $this->BakeArticles->BakeUsers->find('list', ['limit' => 200]);
        $bakeTags = $this->BakeArticles->BakeTags->find('list', ['limit' => 200]);
        $this->set(compact('bakeArticle', 'bakeUsers', 'bakeTags'));
        $this->set('_serialize', ['bakeArticle']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Bake Article id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $bakeArticle = $this->BakeArticles->get($id, [
            'contain' => ['BakeTags']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $bakeArticle = $this->BakeArticles->patchEntity($bakeArticle, $this->request->data);
            if ($this->BakeArticles->save($bakeArticle)) {
                $this->Flash->success(__('The bake article has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The bake article could not be saved. Please, try again.'));
            }
        }
        $bakeUsers = $this->BakeArticles->BakeUsers->find('list', ['limit' => 200]);
        $bakeTags = $this->BakeArticles->BakeTags->find('list', ['limit' => 200]);
        $this->set(compact('bakeArticle', 'bakeUsers', 'bakeTags'));
        $this->set('_serialize', ['bakeArticle']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Bake Article id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $bakeArticle = $this->BakeArticles->get($id);
        if ($this->BakeArticles->delete($bakeArticle)) {
            $this->Flash->success(__('The bake article has been deleted.'));
        } else {
            $this->Flash->error(__('The bake article could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
