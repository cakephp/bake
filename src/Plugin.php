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

use Bake\Command\AllCommand;
use Bake\Command\BehaviorCommand;
use Bake\Command\CellCommand;
use Bake\Command\CommandCommand;
use Bake\Command\ComponentCommand;
use Bake\Command\ControllerAllCommand;
use Bake\Command\ControllerCommand;
use Bake\Command\FixtureAllCommand;
use Bake\Command\FixtureCommand;
use Bake\Command\FormCommand;
use Bake\Command\HelperCommand;
use Bake\Command\MailerCommand;
use Bake\Command\MiddlewareCommand;
use Bake\Command\ModelAllCommand;
use Bake\Command\ModelCommand;
use Bake\Command\PluginCommand;
use Bake\Command\ShellCommand;
use Bake\Command\ShellHelperCommand;
use Bake\Command\TaskCommand;
use Bake\Command\TemplateAllCommand;
use Bake\Command\TemplateCommand;
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
        $commands->add('bake all', AllCommand::class);
        $commands->add('bake behavior', BehaviorCommand::class);
        $commands->add('bake cell', CellCommand::class);
        $commands->add('bake command', CommandCommand::class);
        $commands->add('bake component', ComponentCommand::class);
        $commands->add('bake controller', ControllerCommand::class);
        $commands->add('bake controller all', ControllerAllCommand::class);
        $commands->add('bake fixture', FixtureCommand::class);
        $commands->add('bake fixture all', FixtureAllCommand::class);
        $commands->add('bake form', FormCommand::class);
        $commands->add('bake helper', HelperCommand::class);
        $commands->add('bake mailer', MailerCommand::class);
        $commands->add('bake middleware', MiddlewareCommand::class);
        $commands->add('bake model', ModelCommand::class);
        $commands->add('bake model all', ModelAllCommand::class);
        $commands->add('bake plugin', PluginCommand::class);
        $commands->add('bake shell_helper', ShellHelperCommand::class);
        $commands->add('bake shell', ShellCommand::class);
        $commands->add('bake task', TaskCommand::class);
        $commands->add('bake template', TemplateCommand::class);
        $commands->add('bake template all', TemplateAllCommand::class);
        $commands->add('bake test', TestCommand::class);

        // Add shell for incomplete tasks and backwards compatibility discover.
        $commands->add('bake', BakeShell::class);

        // Add autodiscovery for userland defined commands

        return $commands;
    }
}
