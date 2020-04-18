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

use Bake\Command\BakeCommand;
use Bake\Command\EntryCommand;
use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\Plugin as CorePlugin;
use Cake\Core\PluginApplicationInterface;
use Cake\Routing\RouteBuilder;
use Cake\Utility\Inflector;
use DirectoryIterator;
use ReflectionClass;
use ReflectionException;

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
     * Load the TwigView plugin.
     *
     * @param \Cake\Core\PluginApplicationInterface $app The host application
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        $app->addPlugin('Cake/TwigView');
    }

    /**
     * Define the console commands for an application.
     *
     * @param \Cake\Console\CommandCollection $commands The CommandCollection to add commands into.
     * @return \Cake\Console\CommandCollection The updated collection.
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        // Add commands in plugins and app.
        $commands = $this->discoverCommands($commands);

        // Add entry command to handle entry point and backwards compat.
        $commands->add('bake', EntryCommand::class);

        return $commands;
    }

    /**
     * Scan plugins and application to find commands that are intended
     * to be used with bake.
     *
     * Non-Abstract commands extending `Bake\Command\BakeCommand` are included.
     * Plugins are scanned in the order they are listed in `Plugin::loaded()`
     *
     * @param \Cake\Console\CommandCollection $commands The CommandCollection to add commands into.
     * @return \Cake\Console\CommandCollection The updated collection.
     */
    protected function discoverCommands(CommandCollection $commands): CommandCollection
    {
        foreach (CorePlugin::getCollection()->with('console') as $plugin) {
            $namespace = str_replace('/', '\\', $plugin->getName());
            $pluginPath = $plugin->getClassPath();

            $found = $this->findInPath($namespace, $pluginPath);
            if (count($found)) {
                $commands->addMany($found);
            }
        }

        $found = $this->findInPath(Configure::read('App.namespace'), APP);
        if (count($found)) {
            $commands->addMany($found);
        }

        return $commands;
    }

    /**
     * Search a path for commands.
     *
     * @param string $namespace The namespace classes are expected to be in.
     * @param string $path The path to look in.
     * @return array
     */
    protected function findInPath(string $namespace, string $path): array
    {
        $path .= 'Command/';
        if (!file_exists($path)) {
            return [];
        }
        $iterator = new DirectoryIterator($path);
        $candidates = [];
        foreach ($iterator as $item) {
            if ($item->isDot() || $item->isDir()) {
                continue;
            }
            $class = $namespace . '\Command\\' . $item->getBasename('.php');

            try {
                $reflection = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                continue;
            }
            if (!$reflection->isInstantiable() || !$reflection->isSubclassOf(BakeCommand::class)) {
                continue;
            }

            // Trim off 'Command' from the name.
            [$ns, $className] = namespaceSplit($class);
            $name = Inflector::underscore(substr($className, 0, -7));

            // Commands ending with `_all` should be ` all` instead.
            if (substr($name, -4) === '_all') {
                $name = substr($name, 0, -4) . ' all';
            }
            $candidates["bake {$name}"] = $class;
        }

        return $candidates;
    }
}
