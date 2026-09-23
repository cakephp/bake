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
namespace Bake\Command;

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
    abstract public function name(): string;

    /**
     * Get the generated object's filename without the leading path.
     *
     * @param string $name The name of the object being generated
     * @return string
     */
    abstract public function fileName(string $name): string;

    /**
     * Get the template name.
     *
     * @return string
     */
    abstract public function template(): string;

    /**
     * Get template data.
     *
     * @return array<string, mixed>
     */
    public function templateData(): array
    {
        $namespace = Configure::read('App.namespace');
        if ($this->plugin) {
            $namespace = $this->pluginNamespace($this->plugin);
        }

        return ['namespace' => $namespace];
    }

    /**
     * Execute the command.
     *
     * @return int|null The exit code or null for success
     */
    public function execute(): ?int
    {
        $this->extractCommonProperties($this->args);
        $name = $this->args->getArgumentAt(0);
        if (empty($name)) {
            $this->io->error('You must provide a name to bake a ' . $this->name());
            $this->abort();
        }
        $name = $this->getNameWithoutPrefix($name);
        $name = Inflector::camelize($name);
        $this->bake($name);
        $this->bakeTest($name);

        return static::CODE_SUCCESS;
    }

    /**
     * Generate a class stub
     *
     * @param string $name The class name
     * @return void
     */
    protected function bake(string $name): void
    {
        $contents = $this->createTemplateRenderer()
            ->set('name', $name)
            ->set($this->templateData())
            ->generate($this->template());

        $filename = $this->getPath() . $this->fileName($name);
        $this->io->createFile($filename, $contents, $this->force);

        $emptyFile = $this->getPath() . '.gitkeep';
        $this->deleteEmptyFile($emptyFile);
    }

    /**
     * Generate a test case.
     *
     * @param string $className The class to bake a test for.
     * @return void
     */
    public function bakeTest(string $className): void
    {
        if ($this->args->getOption('no-test')) {
            return;
        }
        $test = new TestCommand();
        $test->plugin = $this->plugin;
        $test->setArgs($this->args);
        $test->setIo($this->io);
        $test->bake($this->name(), $className);
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser Option parser to update.
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = $this->setCommonOptions($parser);
        $name = $this->name();
        $parser->setDescription(
            sprintf('Bake a %s class file.', $name),
        )->addArgument('name', [
            'help' => sprintf(
                'Name of the %s to bake. Can use Plugin.name to bake %s files into plugins.',
                $name,
                $name,
            ),
        ])->addOption('no-test', [
            'boolean' => true,
            'help' => 'Do not generate a test skeleton.',
        ]);

        return $parser;
    }
}
