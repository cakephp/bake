<?php
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

use Bake\Utility\TemplateRenderer;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Utility\Inflector;

/**
 * Base class for simple bake tasks code generator.
 */
abstract class SimpleBakeCommand extends BakeCommand
{
    /**
     * Get the generated object's name.
     *
     * @return string
     */
    abstract public function name();

    /**
     * Get the generated object's filename without the leading path.
     *
     * @param string $name The name of the object being generated
     * @return string
     */
    abstract public function fileName($name);

    /**
     * Get the template name.
     *
     * @return string
     */
    abstract public function template();

    /**
     * Get template data.
     *
     * @param \Cake\Console\Arguments $arguments The arguments for the command
     * @return array
     */
    public function templateData(Arguments $arguments): array
    {
        $namespace = Configure::read('App.namespace');
        if ($this->plugin) {
            $namespace = $this->_pluginNamespace($this->plugin);
        }

        return ['namespace' => $namespace];
    }

    /**
     * Execute the command.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $name = $arguments->getArgument('name');
        if (empty($name)) {
            $this->abort('You must provide a name to bake a ' . $this->name());

            return null;
        }
        $name = $this->_getName($name);
        $name = Inflector::camelize($name);
        $this->bake($name, $arguments, $io);
        $this->bakeTest($arguments);
    }

    /**
     * Generate a class stub
     *
     * @param string $name The class name
     * @param \Cake\Console\Arguments $arguments The console arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return string
     */
    protected function bake(string $name, Arguments $arguments, ConsoleIo $io)
    {
        $renderer = new TemplateRenderer();
        $renderer->set('name', $name);
        $renderer->set($this->templateData($arguments));
        $contents = $renderer->generate($this->template());

        $filename = $this->getPath() . $this->fileName($name);
        $this->createFile($filename, $contents);
        $emptyFile = $this->getPath() . 'empty';
        $this->_deleteEmptyFile($emptyFile);

        return $contents;
    }

    /**
     * Generate a test case.
     *
     * @param string $className The class to bake a test for.
     * @return string|bool|null
     */
    public function bakeTest($className)
    {
        if (!empty($this->params['no-test'])) {
            return null;
        }
        $this->Test->plugin = $this->plugin;

        return $this->Test->bake($this->name(), $className);
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser Option parser to update.
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function buildOptionParser(ConsoleOptionParser $parser)
    {
        $parser = $this->_setCommonOptions($parser);
        $name = $this->name();
        $parser->setDescription(
            sprintf('Bake a %s class file.', $name)
        )->addArgument('name', [
            'help' => sprintf(
                'Name of the %s to bake. Can use Plugin.name to bake %s files into plugins.',
                $name,
                $name
            )
        ])->addOption('no-test', [
            'boolean' => true,
            'help' => 'Do not generate a test skeleton.'
        ]);

        return $parser;
    }
}
