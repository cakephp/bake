<?php
namespace Bake\Test\App\Test\TestCase\Model\Table;

use Bake\Test\App\Model\Table\AuthorsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Bake\Test\App\Model\Table\AuthorsTable Test Case
 */
class AuthorsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Bake\Test\App\Model\Table\AuthorsTable
     */
    public $Authors;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Posts',
        'app.Comments',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Authors') ? [] : ['className' => AuthorsTable::class];
        $this->Authors = TableRegistry::getTableLocator()->get('Authors', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Authors);

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
}
