<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\Model\Table;

use Bake\Test\App\Model\Table\ArticlesTable;
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
     * @var list<string>
     */
    protected array $fixtures = [
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
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Articles') ? [] : ['className' => ArticlesTable::class];
        $this->Articles = $this->getTableLocator()->get('Articles', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Articles);

        parent::tearDown();
    }

    /**
     * Test findPublished method
     *
     * @return void
     * @uses \Bake\Test\App\Model\Table\ArticlesTable::findPublished()
     */
    public function testFindPublished(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test doSomething method
     *
     * @return void
     * @uses \Bake\Test\App\Model\Table\ArticlesTable::doSomething()
     */
    public function testDoSomething(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test doSomethingElse method
     *
     * @return void
     * @uses \Bake\Test\App\Model\Table\ArticlesTable::doSomethingElse()
     */
    public function testDoSomethingElse(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
