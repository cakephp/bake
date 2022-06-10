<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\Command\Helper;

use Bake\Test\App\Command\Helper\ErrorHelper;
use Cake\Console\ConsoleIo;
use Cake\TestSuite\Stub\ConsoleOutput;
use Cake\TestSuite\TestCase;

/**
 * Bake\Test\App\Command\Helper\ErrorHelper Test Case
 */
class ErrorHelperTest extends TestCase
{
    /**
     * ConsoleOutput stub
     *
     * @var \Cake\TestSuite\Stub\ConsoleOutput
     */
    protected $stub;

    /**
     * ConsoleIo mock
     *
     * @var \Cake\Console\ConsoleIo
     */
    protected $io;

    /**
     * Test subject
     *
     * @var \Bake\Test\App\Command\Helper\ErrorHelper
     */
    protected $Error;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->stub = new ConsoleOutput();
        $this->io = new ConsoleIo($this->stub);
        $this->Error = new ErrorHelper($this->io);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Error);

        parent::tearDown();
    }

    /**
     * Test output method
     *
     * @return void
     * @uses \Bake\Test\App\Command\Helper\ErrorHelper::output()
     */
    public function testOutput(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
