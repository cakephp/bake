<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * BakeTags Controller
 *
 */
class BakeTagsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->BakeTags->find();
        $bakeTags = $this->paginate($query);

        $this->set(compact('bakeTags'));
    }

    /**
     * View method
     *
     * @param string|null $id Bake Tag id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $bakeTag = $this->BakeTags->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('bakeTag'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $bakeTag = $this->BakeTags->newEmptyEntity();
        if ($this->request->is('post')) {
            $bakeTag = $this->BakeTags->patchEntity($bakeTag, $this->request->getData());
            if ($this->BakeTags->save($bakeTag)) {
                $this->Flash->success(__('The bake tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bake tag could not be saved. Please, try again.'));
        }
        $this->set(compact('bakeTag'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Bake Tag id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $bakeTag = $this->BakeTags->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $bakeTag = $this->BakeTags->patchEntity($bakeTag, $this->request->getData());
            if ($this->BakeTags->save($bakeTag)) {
                $this->Flash->success(__('The bake tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bake tag could not be saved. Please, try again.'));
        }
        $this->set(compact('bakeTag'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Bake Tag id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $bakeTag = $this->BakeTags->get($id);
        if ($this->BakeTags->delete($bakeTag)) {
            $this->Flash->success(__('The bake tag has been deleted.'));
        } else {
            $this->Flash->error(__('The bake tag could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
