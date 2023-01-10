<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * BakeArticlesBakeTags Controller
 *
 */
class BakeArticlesBakeTagsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->BakeArticlesBakeTags->find();
        $bakeArticlesBakeTags = $this->paginate($query);

        $this->set(compact('bakeArticlesBakeTags'));
    }

    /**
     * View method
     *
     * @param string|null $id Bake Articles Bake Tag id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $bakeArticlesBakeTag = $this->BakeArticlesBakeTags->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('bakeArticlesBakeTag'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $bakeArticlesBakeTag = $this->BakeArticlesBakeTags->newEmptyEntity();
        if ($this->request->is('post')) {
            $bakeArticlesBakeTag = $this->BakeArticlesBakeTags->patchEntity($bakeArticlesBakeTag, $this->request->getData());
            if ($this->BakeArticlesBakeTags->save($bakeArticlesBakeTag)) {
                $this->Flash->success(__('The bake articles bake tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bake articles bake tag could not be saved. Please, try again.'));
        }
        $this->set(compact('bakeArticlesBakeTag'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Bake Articles Bake Tag id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $bakeArticlesBakeTag = $this->BakeArticlesBakeTags->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $bakeArticlesBakeTag = $this->BakeArticlesBakeTags->patchEntity($bakeArticlesBakeTag, $this->request->getData());
            if ($this->BakeArticlesBakeTags->save($bakeArticlesBakeTag)) {
                $this->Flash->success(__('The bake articles bake tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bake articles bake tag could not be saved. Please, try again.'));
        }
        $this->set(compact('bakeArticlesBakeTag'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Bake Articles Bake Tag id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $bakeArticlesBakeTag = $this->BakeArticlesBakeTags->get($id);
        if ($this->BakeArticlesBakeTags->delete($bakeArticlesBakeTag)) {
            $this->Flash->success(__('The bake articles bake tag has been deleted.'));
        } else {
            $this->Flash->error(__('The bake articles bake tag could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
