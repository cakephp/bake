<?php
declare(strict_types=1);
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         1.7.2
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase;

use Bake\Plugin;
use Cake\Console\CommandCollection;
use Cake\Routing\RouteBuilder;
use Cake\Routing\RouteCollection;

/**
 * PluginTest class
 */
class PluginTest extends TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        $this->removePlugins(['BakeTest']);
    }

    public function testRoutes()
    {
        $collection = new RouteCollection();
        $routes = new RouteBuilder($collection, '/');

        $plugin = new Plugin();
        $this->assertNull($plugin->routes($routes));
        $this->assertCount(0, $collection->routes());
    }

    public function testConsoleDiscoverBakeCommands()
    {
        $commands = new CommandCollection();
        $plugin = new Plugin();
        $commands = $plugin->console($commands);

        // Spot check bake commands
        $this->assertTrue($commands->has('bake controller'));
        $this->assertTrue($commands->has('bake controller all'));
        $this->assertTrue($commands->has('bake middleware'));
        $this->assertTrue($commands->has('bake shell_helper'));

        // Ensure base classes are not included
        $this->assertFalse($commands->has('bake bake'));
        $this->assertFalse($commands->has('bake simple_bake'));
        $this->assertFalse($commands->has('bake bake_command'));
    }

    public function testConsoleDiscoverPluginCommands()
    {
        $this->_loadTestPlugin('BakeTest');

        $commands = new CommandCollection();
        $plugin = new Plugin();
        $commands = $plugin->console($commands);

        $this->assertTrue($commands->has('bake zergling'));
        $this->assertFalse($commands->has('bake bake_test.zergling'));
        $this->assertFalse($commands->has('bake BakeTest.zergling'));
    }

    public function testConsoleDiscoverAppCommands()
    {
        $this->setAppNamespace('Bake\Test\App');

        $commands = new CommandCollection();
        $plugin = new Plugin();
        $commands = $plugin->console($commands);

        $this->assertTrue($commands->has('bake custom_controller'));
    }
}
