<?php
namespace App\Test\TestCase\Command;

use App\Command\ExampleCommand;
use Cake\TestSuite\ConsoleIntegrationTestCase;

/**
 * App\Command\ExampleCommand Test Case
 */
class ExampleCommandTest extends ConsoleIntegrationTestCase
{

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->$this->useCommandRunner();
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
