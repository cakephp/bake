<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * HiddenFields Controller
 *
 * @property \Bake\Test\App\Model\Table\HiddenFieldsTable $HiddenFields
 */
class HiddenFieldsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->HiddenFields->find();
        $hiddenFields = $this->paginate($query);

        $this->set(compact('hiddenFields'));
    }

    /**
     * View method
     *
     * @param string|null $id Hidden Field id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $hiddenField = $this->HiddenFields->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('hiddenField'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $hiddenField = $this->HiddenFields->newEmptyEntity();
        if ($this->request->is('post')) {
            $hiddenField = $this->HiddenFields->patchEntity($hiddenField, $this->request->getData());
            if ($this->HiddenFields->save($hiddenField)) {
                $this->Flash->success(__('The hidden field has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The hidden field could not be saved. Please, try again.'));
        }
        $this->set(compact('hiddenField'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Hidden Field id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $hiddenField = $this->HiddenFields->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $hiddenField = $this->HiddenFields->patchEntity($hiddenField, $this->request->getData());
            if ($this->HiddenFields->save($hiddenField)) {
                $this->Flash->success(__('The hidden field has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The hidden field could not be saved. Please, try again.'));
        }
        $this->set(compact('hiddenField'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Hidden Field id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $hiddenField = $this->HiddenFields->get($id);
        if ($this->HiddenFields->delete($hiddenField)) {
            $this->Flash->success(__('The hidden field has been deleted.'));
        } else {
            $this->Flash->error(__('The hidden field could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
