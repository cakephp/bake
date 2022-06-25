<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\Controller\Admin;

use Bake\Test\App\Controller\Admin\PostsController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Bake\Test\App\Controller\Admin\PostsController Test Case
 *
 * @uses \Bake\Test\App\Controller\Admin\PostsController
 */
class PostsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Posts',
    ];

    /**
     * Test index method
     *
     * @return void
     * @uses \Bake\Test\App\Controller\Admin\PostsController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \Bake\Test\App\Controller\Admin\PostsController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
