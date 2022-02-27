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
    protected $io;

    /**
     * Test subject
     *
     * @var \Bake\Test\App\Shell\Task\ArticlesTask
     */
    protected $Articles;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
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
    protected function tearDown(): void
    {
        unset($this->Articles);

        parent::tearDown();
    }
}
