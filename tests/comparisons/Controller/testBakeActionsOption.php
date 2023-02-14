<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

use Cake\Http\Response;

/**
 * BakeArticles Controller
 *
 * @property \Bake\Test\App\Model\Table\BakeArticlesTable $BakeArticles
 * @property \Cake\Controller\Component\FormProtectionComponent $FormProtection
 * @property \Cake\Controller\Component\FlashComponent $Flash
 */
class BakeArticlesController extends AppController
{
    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('FormProtection');
        $this->loadComponent('Flash');
        $this->viewBuilder()->setHelpers(['Html', 'Time']);
    }

    /**
     * Login method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function login()
    {
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);

                return $this->redirect($this->Auth->redirectUrl());
            }
            $this->Flash->error(__('Invalid credentials, try again'));
        }
    }

    /**
     * Logout method
     *
     * @return \Cake\Http\Response|null Redirects to logout URL
     */
    public function logout()
    {
        return $this->redirect($this->Auth->logout());
    }
}
