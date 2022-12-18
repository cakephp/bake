<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         2.0.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Command;

use Bake\Test\TestCase\TestCase;
use Cake\Console\CommandInterface;

/**
 * EntryCommand Test
 */
class EntryCommandTest extends TestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->setAppNamespace('Bake\Test\App');
    }

    /**
     * teardown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->removePlugins(['BakeTest']);
    }

    /**
     * Test execute() generating a full stack
     *
     * @return void
     */
    public function testExecuteHelp()
    {
        $this->exec('bake --help');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertOutputContains('Available Commands');
        $this->assertOutputContains('bake controller');
        $this->assertOutputContains('bake controller all');
        $this->assertOutputContains('bake command');
        $this->assertOutputContains('command_helper');
    }

    /**
     * Test execute() calling an app command
     *
     * @return void
     */
    public function testExecuteAppCommand()
    {
        $this->exec('bake app_policy');
        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertOutputContains('App Policy Generated');
    }

    /**
     * Test calling an app task --help
     *
     * @return void
     */
    public function testExecuteAppTaskHelp()
    {
        $this->exec('bake app_policy --help');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertOutputContains('bake app_policy');
        $this->assertOutputContains('Options');
    }

    /**
     * Test execute() calling a plugin command
     *
     * @return void
     */
    public function testExecutePluginCommand()
    {
        $this->_loadTestPlugin('BakeTest');

        $this->exec('bake zergling --verbose');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertOutputContains('Zerg generated');
        $this->assertOutputContains('Loud noises');
    }

    /**
     * Test execute() error on a missing command
     *
     * @return void
     */
    public function testExecuteMissingCommand()
    {
        $this->exec('bake nope');

        $this->assertExitCode(CommandInterface::CODE_ERROR);
        $this->assertErrorContains('Could not find bake command named `nope`');
    }
}
