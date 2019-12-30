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
 * @since         2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Command;

use Bake\Utility\CommonOptionsTrait;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Core\ConventionsTrait;
use InvalidArgumentException;

/**
 * Base class for commands that bake can use.
 *
 * Classes that extend this class will be auto-discovered by bake
 * and attached as subcommands.
 */
abstract class BakeCommand extends Command
{
    use CommonOptionsTrait;
    use ConventionsTrait;

    /**
     * The pathFragment appended to the plugin/app path.
     *
     * @var string
     */
    protected $pathFragment;

    /**
     * Handles splitting up the plugin prefix and classname.
     *
     * Sets the plugin parameter and plugin property.
     *
     * @param string $name The name to possibly split.
     * @return string The name without the plugin prefix.
     */
    protected function _getName(string $name): string
    {
        if (strpos($name, '.')) {
            [$plugin, $name] = pluginSplit($name);
            $this->plugin = $plugin;
        }

        return $name;
    }

    /**
     * Get the prefix name.
     *
     * Handles camelcasing each namespace in the prefix path.
     *
     * @param \Cake\Console\Arguments $args Arguments instance to read the prefix option from.
     * @return string The inflected prefix path.
     */
    protected function getPrefix(Arguments $args): string
    {
        $prefix = $args->getOption('prefix');
        if (!$prefix) {
            return '';
        }
        $parts = explode('/', $prefix);

        return implode('/', array_map([$this, '_camelize'], $parts));
    }

    /**
     * Gets the path for output. Checks the plugin property
     * and returns the correct path.
     *
     * @param \Cake\Console\Arguments $args Arguments instance to read the prefix option from.
     * @return string Path to output.
     */
    public function getPath(Arguments $args): string
    {
        $path = APP . $this->pathFragment;
        if ($this->plugin) {
            $path = $this->_pluginPath($this->plugin) . 'src/' . $this->pathFragment;
        }
        $prefix = $this->getPrefix($args);
        if ($prefix) {
            $path .= $prefix . DIRECTORY_SEPARATOR;
        }

        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Gets the path to the template path for the application or plugin.
     *
     * @param \Cake\Console\Arguments $args Arguments instance to read the prefix option from.
     * @param string|null $container The container directory in the templates directory.
     * @return string Path to output.
     */
    public function getTemplatePath(Arguments $args, ?string $container = null): string
    {
        $paths = (array)Configure::read('App.paths.templates');
        if (empty($paths)) {
            throw new InvalidArgumentException(
                'Could not read template paths. ' .
                'Ensure `App.paths.templates` is defined in your application configuration.'
            );
        }
        $path = $paths[0];
        if ($this->plugin) {
            $path = $this->_pluginPath($this->plugin) . 'templates' . DIRECTORY_SEPARATOR;
        }
        if ($container) {
            $path .= $container . DIRECTORY_SEPARATOR;
        }
        $prefix = $this->getPrefix($args);
        if ($prefix) {
            $path .= $prefix . DIRECTORY_SEPARATOR;
        }

        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Delete empty file in a given path
     *
     * @param string $path Path to folder which contains 'empty' file.
     * @param \Cake\Console\ConsoleIo $io ConsoleIo to delete file with.
     * @return void
     */
    protected function deleteEmptyFile(string $path, ConsoleIo $io): void
    {
        if (file_exists($path)) {
            unlink($path);
            $io->out(sprintf('<success>Deleted</success> `%s`', $path), 1, ConsoleIo::QUIET);
        }
    }
}
