<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * BakeAuthors Controller
 *
 */
class BakeAuthorsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->BakeAuthors->find();
        $bakeAuthors = $this->paginate($query);

        $this->set(compact('bakeAuthors'));
    }

    /**
     * View method
     *
     * @param string|null $id Bake Author id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $bakeAuthor = $this->BakeAuthors->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('bakeAuthor'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $bakeAuthor = $this->BakeAuthors->newEmptyEntity();
        if ($this->request->is('post')) {
            $bakeAuthor = $this->BakeAuthors->patchEntity($bakeAuthor, $this->request->getData());
            if ($this->BakeAuthors->save($bakeAuthor)) {
                $this->Flash->success(__('The bake author has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bake author could not be saved. Please, try again.'));
        }
        $this->set(compact('bakeAuthor'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Bake Author id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $bakeAuthor = $this->BakeAuthors->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $bakeAuthor = $this->BakeAuthors->patchEntity($bakeAuthor, $this->request->getData());
            if ($this->BakeAuthors->save($bakeAuthor)) {
                $this->Flash->success(__('The bake author has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bake author could not be saved. Please, try again.'));
        }
        $this->set(compact('bakeAuthor'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Bake Author id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $bakeAuthor = $this->BakeAuthors->get($id);
        if ($this->BakeAuthors->delete($bakeAuthor)) {
            $this->Flash->success(__('The bake author has been deleted.'));
        } else {
            $this->Flash->error(__('The bake author could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
