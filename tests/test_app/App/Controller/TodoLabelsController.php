<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * TodoLabels Controller
 *
 */
class TodoLabelsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->TodoLabels->find();
        $todoLabels = $this->paginate($query);

        $this->set(compact('todoLabels'));
    }

    /**
     * View method
     *
     * @param string|null $id Todo Label id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $todoLabel = $this->TodoLabels->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('todoLabel'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $todoLabel = $this->TodoLabels->newEmptyEntity();
        if ($this->request->is('post')) {
            $todoLabel = $this->TodoLabels->patchEntity($todoLabel, $this->request->getData());
            if ($this->TodoLabels->save($todoLabel)) {
                $this->Flash->success(__('The todo label has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The todo label could not be saved. Please, try again.'));
        }
        $this->set(compact('todoLabel'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Todo Label id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $todoLabel = $this->TodoLabels->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $todoLabel = $this->TodoLabels->patchEntity($todoLabel, $this->request->getData());
            if ($this->TodoLabels->save($todoLabel)) {
                $this->Flash->success(__('The todo label has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The todo label could not be saved. Please, try again.'));
        }
        $this->set(compact('todoLabel'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Todo Label id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $todoLabel = $this->TodoLabels->get($id);
        if ($this->TodoLabels->delete($todoLabel)) {
            $this->Flash->success(__('The todo label has been deleted.'));
        } else {
            $this->Flash->error(__('The todo label could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
