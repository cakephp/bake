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
 * @since         0.1.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Shell\Task;

use Bake\Utility\CommonOptionsTrait;
use Bake\Utility\Process;
use Cake\Cache\Cache;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\ConventionsTrait;

/**
 * Base class for Bake Tasks.
 *
 * @deprecated 2.0.0 Support for Tasks will be removed in Bake 3.0
 */
class BakeTask extends Shell
{
    use CommonOptionsTrait;
    use ConventionsTrait;

    /**
     * Table prefix
     *
     * @var string|null
     */
    public $tablePrefix = null;

    /**
     * The pathFragment appended to the plugin/app path.
     *
     * @var string
     */
    public $pathFragment;

    /**
     * Disable caching and enable debug for baking.
     * This forces the most current database schema to be used.
     *
     * @return void
     */
    public function startup(): void
    {
        Configure::write('debug', true);
        Cache::disable();
    }

    /**
     * Initialize hook.
     *
     * Populates the connection property, which is useful for tasks of tasks.
     *
     * @return void
     */
    public function initialize(): void
    {
        if (empty($this->connection) && !empty($this->params['connection'])) {
            $this->connection = $this->params['connection'];
        }
    }

    /**
     * Get the prefix name.
     *
     * Handles camelcasing each namespace in the prefix path.
     *
     * @return string The inflected prefix path.
     */
    protected function _getPrefix(): string
    {
        $prefix = $this->param('prefix');
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
     * @return string Path to output.
     */
    public function getPath(): string
    {
        $path = APP . $this->pathFragment;
        if ($this->plugin) {
            $path = $this->_pluginPath($this->plugin) . 'src/' . $this->pathFragment;
        }
        $prefix = $this->_getPrefix();
        if ($prefix) {
            $path .= $prefix . DS;
        }

        return str_replace('/', DS, $path);
    }

    /**
     * Base execute method parses some parameters and sets some properties on the bake tasks.
     * call when overriding execute()
     *
     * @return int|null
     */
    public function main()
    {
        if (isset($this->params['plugin'])) {
            $parts = explode('/', $this->params['plugin']);
            $this->plugin = implode('/', array_map([$this, '_camelize'], $parts));
            if (strpos($this->plugin, '\\')) {
                $this->abort('Invalid plugin namespace separator, please use / instead of \ for plugins.');
            }
        }
        if (isset($this->params['connection'])) {
            $this->connection = $this->params['connection'];
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Executes an external shell command and pipes its output to the stdout
     *
     * @param string $command the command to execute
     * @return void
     * @throws \RuntimeException if any errors occurred during the execution
     */
    public function callProcess(string $command): void
    {
        $process = new Process($this->_io);
        $out = $process->call($command);
        $this->out($out);
    }

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
        if (empty($name)) {
            return $name;
        }

        if (strpos($name, '.')) {
            [$plugin, $name] = pluginSplit($name);
            $this->plugin = $this->params['plugin'] = $plugin;
        }

        return $name;
    }

    /**
     * Delete empty file in a given path
     *
     * @param string $path Path to folder which contains 'empty' file.
     * @return void
     */
    protected function _deleteEmptyFile(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
            $this->out(sprintf('<success>Deleted</success> `%s`', $path), 1, Shell::QUIET);
        }
    }

    /**
     * Get the option parser for this task.
     *
     * This base class method sets up some commonly used options.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser(): ConsoleOptionParser
    {
        return $this->_setCommonOptions(parent::getOptionParser());
    }
}
