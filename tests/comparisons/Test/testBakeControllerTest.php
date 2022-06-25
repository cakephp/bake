<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\Controller;

use Bake\Test\App\Controller\PostsController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Bake\Test\App\Controller\PostsController Test Case
 *
 * @uses \Bake\Test\App\Controller\PostsController
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
     * @uses \Bake\Test\App\Controller\PostsController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
