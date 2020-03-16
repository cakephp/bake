<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\Model\Table;

use Bake\Test\App\Model\Table\ArticlesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Bake\Test\App\Model\Table\ArticlesTable Test Case
 */
class ArticlesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Bake\Test\App\Model\Table\ArticlesTable
     */
    protected $Articles;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Articles',
        'app.Authors',
        'app.Tags',
        'app.ArticlesTags',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Articles') ? [] : ['className' => ArticlesTable::class];
        $this->Articles = TableRegistry::getTableLocator()->get('Articles', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Articles);

        parent::tearDown();
    }

    /**
     * Test findPublished method
     *
     * @return void
     */
    public function testFindPublished(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test doSomething method
     *
     * @return void
     */
    public function testDoSomething(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test doSomethingElse method
     *
     * @return void
     */
    public function testDoSomethingElse(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
