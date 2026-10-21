<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

use Cake\Routing\Attribute\Route;
use Cake\Routing\Attribute\Scope;

/**
 * BakeArticles Controller
 *
 * @property \Bake\Test\App\Model\Table\BakeArticlesTable $BakeArticles
 * @property \Cake\Controller\Component\FormProtectionComponent $FormProtection
 * @property \Cake\Controller\Component\FlashComponent $Flash
 */
#[Scope('/bake-articles')]
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
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    #[Route('/')]
    #[Route('/index')]
    public function index()
    {
        $query = $this->BakeArticles->find()
            ->contain(['BakeUsers']);
        $bakeArticles = $this->paginate($query);

        $this->set(compact('bakeArticles'));
    }
}
