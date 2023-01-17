<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * TodoItemsTodoLabels Controller
 *
 */
class TodoItemsTodoLabelsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->TodoItemsTodoLabels->find();
        $todoItemsTodoLabels = $this->paginate($query);

        $this->set(compact('todoItemsTodoLabels'));
    }

    /**
     * View method
     *
     * @param string|null $id Todo Items Todo Label id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $todoItemsTodoLabel = $this->TodoItemsTodoLabels->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('todoItemsTodoLabel'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $todoItemsTodoLabel = $this->TodoItemsTodoLabels->newEmptyEntity();
        if ($this->request->is('post')) {
            $todoItemsTodoLabel = $this->TodoItemsTodoLabels->patchEntity($todoItemsTodoLabel, $this->request->getData());
            if ($this->TodoItemsTodoLabels->save($todoItemsTodoLabel)) {
                $this->Flash->success(__('The todo items todo label has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The todo items todo label could not be saved. Please, try again.'));
        }
        $this->set(compact('todoItemsTodoLabel'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Todo Items Todo Label id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $todoItemsTodoLabel = $this->TodoItemsTodoLabels->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $todoItemsTodoLabel = $this->TodoItemsTodoLabels->patchEntity($todoItemsTodoLabel, $this->request->getData());
            if ($this->TodoItemsTodoLabels->save($todoItemsTodoLabel)) {
                $this->Flash->success(__('The todo items todo label has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The todo items todo label could not be saved. Please, try again.'));
        }
        $this->set(compact('todoItemsTodoLabel'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Todo Items Todo Label id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $todoItemsTodoLabel = $this->TodoItemsTodoLabels->get($id);
        if ($this->TodoItemsTodoLabels->delete($todoItemsTodoLabel)) {
            $this->Flash->success(__('The todo items todo label has been deleted.'));
        } else {
            $this->Flash->error(__('The todo items todo label could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
