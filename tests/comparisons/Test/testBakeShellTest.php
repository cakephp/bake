<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\Shell;

use Bake\Test\App\Shell\ArticlesShell;
use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Bake\Test\App\Shell\ArticlesShell Test Case
 */
class ArticlesShellTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * ConsoleIo mock
     *
     * @var \Cake\Console\ConsoleIo|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $io;

    /**
     * Test subject
     *
     * @var \Bake\Test\App\Shell\ArticlesShell
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
        $this->Articles = new ArticlesShell($this->io);
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
