<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.7.2
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase;

use Bake\BakePlugin;
use Cake\Console\CommandCollection;
use Cake\Routing\RouteBuilder;
use Cake\Routing\RouteCollection;

/**
 * PluginTest class
 */
class PluginTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
        $this->removePlugins(['BakeTest', 'WithBakeSubFolder']);
    }

    public function testRoutes()
    {
        $collection = new RouteCollection();
        $routes = new RouteBuilder($collection, '/');

        $plugin = new BakePlugin();
        $this->assertNull($plugin->routes($routes));
        $this->assertCount(0, $collection->routes());
    }

    public function testConsoleDiscoverBakeCommands()
    {
        $commands = new CommandCollection();
        $plugin = new BakePlugin();
        $commands = $plugin->console($commands);

        // Spot check bake commands
        $this->assertTrue($commands->has('bake controller'));
        $this->assertTrue($commands->has('bake controller all'));
        $this->assertTrue($commands->has('bake middleware'));
        $this->assertTrue($commands->has('bake command_helper'));

        // Ensure base classes are not included
        $this->assertFalse($commands->has('bake bake'));
        $this->assertFalse($commands->has('bake simple_bake'));
        $this->assertFalse($commands->has('bake bake_command'));
    }

    public function testConsoleDiscoverPluginCommands()
    {
        $this->_loadTestPlugin('BakeTest');

        $commands = new CommandCollection();
        $plugin = new BakePlugin();
        $commands = $plugin->console($commands);

        $this->assertTrue($commands->has('bake zergling'));
        $this->assertFalse($commands->has('bake bake_test.zergling'));
        $this->assertFalse($commands->has('bake BakeTest.zergling'));
    }

    public function testConsoleDiscoverPluginCommandsInSubFolder()
    {
        $this->_loadTestPlugin('WithBakeSubFolder');

        $commands = new CommandCollection();
        $plugin = new BakePlugin();
        $commands = $plugin->console($commands);

        $this->assertTrue($commands->has('bake led_zepplin'));
        $this->assertFalse(
            $commands->has('bake the_who'),
            'Only commands from "Bake" subfolder should be loaded'
        );
    }

    public function testConsoleDiscoverAppCommands()
    {
        $this->setAppNamespace('Bake\Test\App');

        $commands = new CommandCollection();
        $plugin = new BakePlugin();
        $commands = $plugin->console($commands);

        $this->assertTrue($commands->has('bake custom_controller'));
    }
}
