<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * BakeUsers Controller
 *
 */
class BakeUsersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->BakeUsers->find();
        $bakeUsers = $this->paginate($query);

        $this->set(compact('bakeUsers'));
    }

    /**
     * View method
     *
     * @param string|null $id Bake User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $bakeUser = $this->BakeUsers->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('bakeUser'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $bakeUser = $this->BakeUsers->newEmptyEntity();
        if ($this->request->is('post')) {
            $bakeUser = $this->BakeUsers->patchEntity($bakeUser, $this->request->getData());
            if ($this->BakeUsers->save($bakeUser)) {
                $this->Flash->success(__('The bake user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bake user could not be saved. Please, try again.'));
        }
        $this->set(compact('bakeUser'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Bake User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $bakeUser = $this->BakeUsers->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $bakeUser = $this->BakeUsers->patchEntity($bakeUser, $this->request->getData());
            if ($this->BakeUsers->save($bakeUser)) {
                $this->Flash->success(__('The bake user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bake user could not be saved. Please, try again.'));
        }
        $this->set(compact('bakeUser'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Bake User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $bakeUser = $this->BakeUsers->get($id);
        if ($this->BakeUsers->delete($bakeUser)) {
            $this->Flash->success(__('The bake user has been deleted.'));
        } else {
            $this->Flash->error(__('The bake user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
