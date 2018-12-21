<?php
declare(strict_types=1);
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         1.7.2
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake;

use Bake\Command\BehaviorCommand;
use Bake\Command\CellCommand;
use Bake\Command\CommandCommand;
use Bake\Command\ComponentCommand;
use Bake\Command\FormCommand;
use Bake\Command\HelperCommand;
use Bake\Command\TestCommand;
use Bake\Shell\BakeShell;
use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use Cake\Routing\RouteBuilder;

/**
 * Plugin class for bake
 */
class Plugin extends BasePlugin
{
    /**
     * Plugin name.
     *
     * @var string
     */
    protected $name = 'Bake';

    /**
     * Override to do nothing.
     *
     * @param \Cake\Routing\RouteBuilder $routes The route builder to update.
     * @return void
     */
    public function routes(RouteBuilder $routes): void
    {
    }

    /**
     * load WyriHaximus/TwigView plugin.
     *
     * @param \Cake\Core\PluginApplicationInterface $app The host application
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        $app->addPlugin('WyriHaximus/TwigView');
    }

    /**
     * Define the console commands for an application.
     *
     * @param \Cake\Console\CommandCollection $commands The CommandCollection to add commands into.
     * @return \Cake\Console\CommandCollection The updated collection.
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        $commands->add('bake behavior', BehaviorCommand::class);
        $commands->add('bake cell', CellCommand::class);
        $commands->add('bake command', CommandCommand::class);
        $commands->add('bake component', ComponentCommand::class);
        $commands->add('bake form', FormCommand::class);
        $commands->add('bake helper', HelperCommand::class);
        $commands->add('bake test', TestCommand::class);

        // Add shell for incomplete tasks and backwards compatibility discover.
        $commands->add('bake', BakeShell::class);

        return $commands;
    }
}
