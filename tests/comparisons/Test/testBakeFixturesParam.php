<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\Model\Table;

use Bake\Test\App\Model\Table\AuthorsTable;
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
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Posts',
        'app.Comments',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Authors') ? [] : ['className' => AuthorsTable::class];
        $this->Authors = $this->getTableLocator()->get('Authors', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Authors);

        parent::tearDown();
    }
}
