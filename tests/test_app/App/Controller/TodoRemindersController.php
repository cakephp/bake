<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * TodoReminders Controller
 *
 */
class TodoRemindersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->TodoReminders->find();
        $todoReminders = $this->paginate($query);

        $this->set(compact('todoReminders'));
    }

    /**
     * View method
     *
     * @param string|null $id Todo Reminder id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $todoReminder = $this->TodoReminders->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('todoReminder'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $todoReminder = $this->TodoReminders->newEmptyEntity();
        if ($this->request->is('post')) {
            $todoReminder = $this->TodoReminders->patchEntity($todoReminder, $this->request->getData());
            if ($this->TodoReminders->save($todoReminder)) {
                $this->Flash->success(__('The todo reminder has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The todo reminder could not be saved. Please, try again.'));
        }
        $this->set(compact('todoReminder'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Todo Reminder id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $todoReminder = $this->TodoReminders->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $todoReminder = $this->TodoReminders->patchEntity($todoReminder, $this->request->getData());
            if ($this->TodoReminders->save($todoReminder)) {
                $this->Flash->success(__('The todo reminder has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The todo reminder could not be saved. Please, try again.'));
        }
        $this->set(compact('todoReminder'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Todo Reminder id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $todoReminder = $this->TodoReminders->get($id);
        if ($this->TodoReminders->delete($todoReminder)) {
            $this->Flash->success(__('The todo reminder has been deleted.'));
        } else {
            $this->Flash->error(__('The todo reminder could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
