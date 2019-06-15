<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * BakeArticles Controller
 *
 *
 * @method \App\Model\Entity\BakeArticle[] paginate($object = null, array $settings = [])
 */
class BakeArticlesController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['BakeUsers']
        ];
        $bakeArticles = $this->paginate($this->BakeArticles);

        $this->set(compact('bakeArticles'));
    }

    /**
     * View method
     *
     * @param string|null $id Bake Article id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $bakeArticle = $this->BakeArticles->get($id, [
            'contain' => ['BakeUsers', 'BakeTags', 'BakeComments']
        ]);

        $this->set('bakeArticle', $bakeArticle);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $bakeArticle = $this->BakeArticles->newEntity();
        if ($this->getRequest()->is('post')) {
            $bakeArticle = $this->BakeArticles->patchEntity($bakeArticle, $this->getRequest()->getData());
            if ($this->BakeArticles->save($bakeArticle)) {
                $this->Flash->success(__('The bake article has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bake article could not be saved. Please, try again.'));
        }
        $bakeUsers = $this->BakeArticles->BakeUsers->find('list', ['limit' => 200]);
        $bakeTags = $this->BakeArticles->BakeTags->find('list', ['limit' => 200]);
        $this->set(compact('bakeArticle', 'bakeUsers', 'bakeTags'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Bake Article id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $bakeArticle = $this->BakeArticles->get($id, [
            'contain' => ['BakeTags']
        ]);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $bakeArticle = $this->BakeArticles->patchEntity($bakeArticle, $this->getRequest()->getData());
            if ($this->BakeArticles->save($bakeArticle)) {
                $this->Flash->success(__('The bake article has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bake article could not be saved. Please, try again.'));
        }
        $bakeUsers = $this->BakeArticles->BakeUsers->find('list', ['limit' => 200]);
        $bakeTags = $this->BakeArticles->BakeTags->find('list', ['limit' => 200]);
        $this->set(compact('bakeArticle', 'bakeUsers', 'bakeTags'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Bake Article id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $bakeArticle = $this->BakeArticles->get($id);
        if ($this->BakeArticles->delete($bakeArticle)) {
            $this->Flash->success(__('The bake article has been deleted.'));
        } else {
            $this->Flash->error(__('The bake article could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
