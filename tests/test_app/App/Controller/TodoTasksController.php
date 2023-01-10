<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * TodoTasks Controller
 *
 */
class TodoTasksController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->TodoTasks->find();
        $todoTasks = $this->paginate($query);

        $this->set(compact('todoTasks'));
    }

    /**
     * View method
     *
     * @param string|null $id Todo Task id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $todoTask = $this->TodoTasks->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('todoTask'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $todoTask = $this->TodoTasks->newEmptyEntity();
        if ($this->request->is('post')) {
            $todoTask = $this->TodoTasks->patchEntity($todoTask, $this->request->getData());
            if ($this->TodoTasks->save($todoTask)) {
                $this->Flash->success(__('The todo task has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The todo task could not be saved. Please, try again.'));
        }
        $this->set(compact('todoTask'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Todo Task id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $todoTask = $this->TodoTasks->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $todoTask = $this->TodoTasks->patchEntity($todoTask, $this->request->getData());
            if ($this->TodoTasks->save($todoTask)) {
                $this->Flash->success(__('The todo task has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The todo task could not be saved. Please, try again.'));
        }
        $this->set(compact('todoTask'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Todo Task id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $todoTask = $this->TodoTasks->get($id);
        if ($this->TodoTasks->delete($todoTask)) {
            $this->Flash->success(__('The todo task has been deleted.'));
        } else {
            $this->Flash->error(__('The todo task could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
