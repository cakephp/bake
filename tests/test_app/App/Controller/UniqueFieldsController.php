<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * UniqueFields Controller
 *
 */
class UniqueFieldsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->UniqueFields->find();
        $uniqueFields = $this->paginate($query);

        $this->set(compact('uniqueFields'));
    }

    /**
     * View method
     *
     * @param string|null $id Unique Field id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $uniqueField = $this->UniqueFields->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('uniqueField'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $uniqueField = $this->UniqueFields->newEmptyEntity();
        if ($this->request->is('post')) {
            $uniqueField = $this->UniqueFields->patchEntity($uniqueField, $this->request->getData());
            if ($this->UniqueFields->save($uniqueField)) {
                $this->Flash->success(__('The unique field has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The unique field could not be saved. Please, try again.'));
        }
        $this->set(compact('uniqueField'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Unique Field id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $uniqueField = $this->UniqueFields->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $uniqueField = $this->UniqueFields->patchEntity($uniqueField, $this->request->getData());
            if ($this->UniqueFields->save($uniqueField)) {
                $this->Flash->success(__('The unique field has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The unique field could not be saved. Please, try again.'));
        }
        $this->set(compact('uniqueField'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Unique Field id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $uniqueField = $this->UniqueFields->get($id);
        if ($this->UniqueFields->delete($uniqueField)) {
            $this->Flash->success(__('The unique field has been deleted.'));
        } else {
            $this->Flash->error(__('The unique field could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
