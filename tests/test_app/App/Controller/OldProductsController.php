<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * OldProducts Controller
 *
 */
class OldProductsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->OldProducts->find();
        $oldProducts = $this->paginate($query);

        $this->set(compact('oldProducts'));
    }

    /**
     * View method
     *
     * @param string|null $id Old Product id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $oldProduct = $this->OldProducts->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('oldProduct'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $oldProduct = $this->OldProducts->newEmptyEntity();
        if ($this->request->is('post')) {
            $oldProduct = $this->OldProducts->patchEntity($oldProduct, $this->request->getData());
            if ($this->OldProducts->save($oldProduct)) {
                $this->Flash->success(__('The old product has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The old product could not be saved. Please, try again.'));
        }
        $this->set(compact('oldProduct'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Old Product id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $oldProduct = $this->OldProducts->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $oldProduct = $this->OldProducts->patchEntity($oldProduct, $this->request->getData());
            if ($this->OldProducts->save($oldProduct)) {
                $this->Flash->success(__('The old product has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The old product could not be saved. Please, try again.'));
        }
        $this->set(compact('oldProduct'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Old Product id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $oldProduct = $this->OldProducts->get($id);
        if ($this->OldProducts->delete($oldProduct)) {
            $this->Flash->success(__('The old product has been deleted.'));
        } else {
            $this->Flash->error(__('The old product could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
