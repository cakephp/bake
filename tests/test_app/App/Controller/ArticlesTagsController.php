<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * ArticlesTags Controller
 *
 */
class ArticlesTagsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->ArticlesTags->find();
        $articlesTags = $this->paginate($query);

        $this->set(compact('articlesTags'));
    }

    /**
     * View method
     *
     * @param string|null $id Articles Tag id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $articlesTag = $this->ArticlesTags->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('articlesTag'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $articlesTag = $this->ArticlesTags->newEmptyEntity();
        if ($this->request->is('post')) {
            $articlesTag = $this->ArticlesTags->patchEntity($articlesTag, $this->request->getData());
            if ($this->ArticlesTags->save($articlesTag)) {
                $this->Flash->success(__('The articles tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The articles tag could not be saved. Please, try again.'));
        }
        $this->set(compact('articlesTag'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Articles Tag id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $articlesTag = $this->ArticlesTags->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $articlesTag = $this->ArticlesTags->patchEntity($articlesTag, $this->request->getData());
            if ($this->ArticlesTags->save($articlesTag)) {
                $this->Flash->success(__('The articles tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The articles tag could not be saved. Please, try again.'));
        }
        $this->set(compact('articlesTag'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Articles Tag id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $articlesTag = $this->ArticlesTags->get($id);
        if ($this->ArticlesTags->delete($articlesTag)) {
            $this->Flash->success(__('The articles tag has been deleted.'));
        } else {
            $this->Flash->error(__('The articles tag could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
