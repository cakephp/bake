<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * SelfReferencingUniqueKeys Controller
 *
 */
class SelfReferencingUniqueKeysController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->SelfReferencingUniqueKeys->find();
        $selfReferencingUniqueKeys = $this->paginate($query);

        $this->set(compact('selfReferencingUniqueKeys'));
    }

    /**
     * View method
     *
     * @param string|null $id Self Referencing Unique Key id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $selfReferencingUniqueKey = $this->SelfReferencingUniqueKeys->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('selfReferencingUniqueKey'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $selfReferencingUniqueKey = $this->SelfReferencingUniqueKeys->newEmptyEntity();
        if ($this->request->is('post')) {
            $selfReferencingUniqueKey = $this->SelfReferencingUniqueKeys->patchEntity($selfReferencingUniqueKey, $this->request->getData());
            if ($this->SelfReferencingUniqueKeys->save($selfReferencingUniqueKey)) {
                $this->Flash->success(__('The self referencing unique key has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The self referencing unique key could not be saved. Please, try again.'));
        }
        $this->set(compact('selfReferencingUniqueKey'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Self Referencing Unique Key id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $selfReferencingUniqueKey = $this->SelfReferencingUniqueKeys->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $selfReferencingUniqueKey = $this->SelfReferencingUniqueKeys->patchEntity($selfReferencingUniqueKey, $this->request->getData());
            if ($this->SelfReferencingUniqueKeys->save($selfReferencingUniqueKey)) {
                $this->Flash->success(__('The self referencing unique key has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The self referencing unique key could not be saved. Please, try again.'));
        }
        $this->set(compact('selfReferencingUniqueKey'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Self Referencing Unique Key id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $selfReferencingUniqueKey = $this->SelfReferencingUniqueKeys->get($id);
        if ($this->SelfReferencingUniqueKeys->delete($selfReferencingUniqueKey)) {
            $this->Flash->success(__('The self referencing unique key has been deleted.'));
        } else {
            $this->Flash->error(__('The self referencing unique key could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
