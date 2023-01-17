<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * BinaryTests Controller
 *
 */
class BinaryTestsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->BinaryTests->find();
        $binaryTests = $this->paginate($query);

        $this->set(compact('binaryTests'));
    }

    /**
     * View method
     *
     * @param string|null $id Binary Test id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $binaryTest = $this->BinaryTests->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('binaryTest'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $binaryTest = $this->BinaryTests->newEmptyEntity();
        if ($this->request->is('post')) {
            $binaryTest = $this->BinaryTests->patchEntity($binaryTest, $this->request->getData());
            if ($this->BinaryTests->save($binaryTest)) {
                $this->Flash->success(__('The binary test has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The binary test could not be saved. Please, try again.'));
        }
        $this->set(compact('binaryTest'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Binary Test id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $binaryTest = $this->BinaryTests->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $binaryTest = $this->BinaryTests->patchEntity($binaryTest, $this->request->getData());
            if ($this->BinaryTests->save($binaryTest)) {
                $this->Flash->success(__('The binary test has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The binary test could not be saved. Please, try again.'));
        }
        $this->set(compact('binaryTest'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Binary Test id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $binaryTest = $this->BinaryTests->get($id);
        if ($this->BinaryTests->delete($binaryTest)) {
            $this->Flash->success(__('The binary test has been deleted.'));
        } else {
            $this->Flash->error(__('The binary test could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
