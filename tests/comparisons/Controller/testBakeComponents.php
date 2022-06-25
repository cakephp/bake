<?php
declare(strict_types=1);

namespace Bake\Test\App\Controller;

/**
 * BakeArticles Controller
 *
 * @property \Bake\Test\App\Model\Table\BakeArticlesTable $BakeArticles
 * @property \Cake\Controller\Component\FormProtectionComponent $FormProtection
 * @property \Cake\Controller\Component\FlashComponent $Flash
 * @property \Company\TestBakeThree\Controller\Component\SomethingComponent $Something
 * @property \TestBake\Controller\Component\OtherComponent $Other
 * @property \Bake\Test\App\Controller\Component\AppleComponent $Apple
 * @property \Bake\Test\App\Controller\Component\NonExistentComponent $NonExistent
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
        $this->loadComponent('Company/TestBakeThree.Something');
        $this->loadComponent('TestBake.Other');
        $this->loadComponent('Apple');
        $this->loadComponent('NonExistent');
    }
}
