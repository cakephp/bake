<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * CategoryThreads Controller
 *
 * @property \Bake\Test\App\Model\Table\CategoryThreadsTable $CategoryThreads
 */
class CategoryThreadsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->CategoryThreads->find()
            ->contain(['ParentCategoryThreads']);
        $categoryThreads = $this->paginate($query);

        $this->set(compact('categoryThreads'));
    }

    /**
     * View method
     *
     * @param string|null $id Category Thread id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $categoryThread = $this->CategoryThreads->get($id, [
            'contain' => ['ParentCategoryThreads', 'ChildCategoryThreads'],
        ]);

        $this->set(compact('categoryThread'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $categoryThread = $this->CategoryThreads->newEmptyEntity();
        if ($this->request->is('post')) {
            $categoryThread = $this->CategoryThreads->patchEntity($categoryThread, $this->request->getData());
            if ($this->CategoryThreads->save($categoryThread)) {
                $this->Flash->success(__('The category thread has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The category thread could not be saved. Please, try again.'));
        }
        $parentCategoryThreads = $this->CategoryThreads->ParentCategoryThreads->find('list', ['limit' => 200])->all();
        $this->set(compact('categoryThread', 'parentCategoryThreads'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Category Thread id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $categoryThread = $this->CategoryThreads->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $categoryThread = $this->CategoryThreads->patchEntity($categoryThread, $this->request->getData());
            if ($this->CategoryThreads->save($categoryThread)) {
                $this->Flash->success(__('The category thread has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The category thread could not be saved. Please, try again.'));
        }
        $parentCategoryThreads = $this->CategoryThreads->ParentCategoryThreads->find('list', ['limit' => 200])->all();
        $this->set(compact('categoryThread', 'parentCategoryThreads'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Category Thread id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $categoryThread = $this->CategoryThreads->get($id);
        if ($this->CategoryThreads->delete($categoryThread)) {
            $this->Flash->success(__('The category thread has been deleted.'));
        } else {
            $this->Flash->error(__('The category thread could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
