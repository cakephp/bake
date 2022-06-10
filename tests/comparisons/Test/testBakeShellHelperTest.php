<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\Shell\Helper;

use Bake\Test\App\Shell\Helper\ExampleHelper;
use Cake\Console\ConsoleIo;
use Cake\TestSuite\Stub\ConsoleOutput;
use Cake\TestSuite\TestCase;

/**
 * Bake\Test\App\Shell\Helper\ExampleHelper Test Case
 */
class ExampleHelperTest extends TestCase
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
     * @var \Bake\Test\App\Shell\Helper\ExampleHelper
     */
    protected $Example;

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
        $this->Example = new ExampleHelper($this->io);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Example);

        parent::tearDown();
    }
}
