<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * Datatypes Controller
 *
 */
class DatatypesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Datatypes->find();
        $datatypes = $this->paginate($query);

        $this->set(compact('datatypes'));
    }

    /**
     * View method
     *
     * @param string|null $id Datatype id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $datatype = $this->Datatypes->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('datatype'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $datatype = $this->Datatypes->newEmptyEntity();
        if ($this->request->is('post')) {
            $datatype = $this->Datatypes->patchEntity($datatype, $this->request->getData());
            if ($this->Datatypes->save($datatype)) {
                $this->Flash->success(__('The datatype has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The datatype could not be saved. Please, try again.'));
        }
        $this->set(compact('datatype'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Datatype id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $datatype = $this->Datatypes->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $datatype = $this->Datatypes->patchEntity($datatype, $this->request->getData());
            if ($this->Datatypes->save($datatype)) {
                $this->Flash->success(__('The datatype has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The datatype could not be saved. Please, try again.'));
        }
        $this->set(compact('datatype'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Datatype id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $datatype = $this->Datatypes->get($id);
        if ($this->Datatypes->delete($datatype)) {
            $this->Flash->success(__('The datatype has been deleted.'));
        } else {
            $this->Flash->error(__('The datatype could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
