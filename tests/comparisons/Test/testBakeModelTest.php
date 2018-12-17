<?php
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
    public $Articles;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Articles',
        'app.Authors',
        'app.Tags',
        'app.ArticlesTags'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
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
    public function tearDown()
    {
        unset($this->Articles);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findPublished method
     *
     * @return void
     */
    public function testFindPublished()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test doSomething method
     *
     * @return void
     */
    public function testDoSomething()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test doSomethingElse method
     *
     * @return void
     */
    public function testDoSomethingElse()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
