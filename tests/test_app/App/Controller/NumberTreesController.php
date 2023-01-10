<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * NumberTrees Controller
 *
 */
class NumberTreesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->NumberTrees->find();
        $numberTrees = $this->paginate($query);

        $this->set(compact('numberTrees'));
    }

    /**
     * View method
     *
     * @param string|null $id Number Tree id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $numberTree = $this->NumberTrees->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('numberTree'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $numberTree = $this->NumberTrees->newEmptyEntity();
        if ($this->request->is('post')) {
            $numberTree = $this->NumberTrees->patchEntity($numberTree, $this->request->getData());
            if ($this->NumberTrees->save($numberTree)) {
                $this->Flash->success(__('The number tree has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The number tree could not be saved. Please, try again.'));
        }
        $this->set(compact('numberTree'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Number Tree id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $numberTree = $this->NumberTrees->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $numberTree = $this->NumberTrees->patchEntity($numberTree, $this->request->getData());
            if ($this->NumberTrees->save($numberTree)) {
                $this->Flash->success(__('The number tree has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The number tree could not be saved. Please, try again.'));
        }
        $this->set(compact('numberTree'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Number Tree id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $numberTree = $this->NumberTrees->get($id);
        if ($this->NumberTrees->delete($numberTree)) {
            $this->Flash->success(__('The number tree has been deleted.'));
        } else {
            $this->Flash->error(__('The number tree could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
