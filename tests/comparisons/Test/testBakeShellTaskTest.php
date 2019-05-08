<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\Shell\Task;

use Bake\Test\App\Shell\Task\ArticlesTask;
use Cake\TestSuite\TestCase;

/**
 * Bake\Test\App\Shell\Task\ArticlesTask Test Case
 */
class ArticlesTaskTest extends TestCase
{
    /**
     * ConsoleIo mock
     *
     * @var \Cake\Console\ConsoleIo|\PHPUnit\Framework\MockObject\MockObject
     */
    public $io;

    /**
     * Test subject
     *
     * @var \Bake\Test\App\Shell\Task\ArticlesTask
     */
    public $Articles;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->io = $this->getMockBuilder('Cake\Console\ConsoleIo')->getMock();
        $this->Articles = new ArticlesTask($this->io);
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
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
