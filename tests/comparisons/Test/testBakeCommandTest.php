<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\Command;

use Bake\Test\App\Command\OtherExampleCommand;
use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Bake\Test\App\Command\OtherExampleCommand Test Case
 *
 * @uses \Bake\Test\App\Command\OtherExampleCommand
 */
class OtherExampleCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->useCommandRunner();
    }
}
