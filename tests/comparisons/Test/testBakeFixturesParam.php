<?php
declare(strict_types=1);

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
    protected $Authors;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Posts',
        'app.Comments',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
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
    public function tearDown(): void
    {
        unset($this->Authors);

        parent::tearDown();
    }
}
