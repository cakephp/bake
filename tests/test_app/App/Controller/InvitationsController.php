<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * Invitations Controller
 *
 */
class InvitationsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Invitations->find();
        $invitations = $this->paginate($query);

        $this->set(compact('invitations'));
    }

    /**
     * View method
     *
     * @param string|null $id Invitation id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $invitation = $this->Invitations->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('invitation'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $invitation = $this->Invitations->newEmptyEntity();
        if ($this->request->is('post')) {
            $invitation = $this->Invitations->patchEntity($invitation, $this->request->getData());
            if ($this->Invitations->save($invitation)) {
                $this->Flash->success(__('The invitation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The invitation could not be saved. Please, try again.'));
        }
        $this->set(compact('invitation'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Invitation id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $invitation = $this->Invitations->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $invitation = $this->Invitations->patchEntity($invitation, $this->request->getData());
            if ($this->Invitations->save($invitation)) {
                $this->Flash->success(__('The invitation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The invitation could not be saved. Please, try again.'));
        }
        $this->set(compact('invitation'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Invitation id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $invitation = $this->Invitations->get($id);
        if ($this->Invitations->delete($invitation)) {
            $this->Flash->success(__('The invitation has been deleted.'));
        } else {
            $this->Flash->error(__('The invitation could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
