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

use Bake\Utility\TemplateRenderer;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Utility\Inflector;

/**
 * Base class for simple bake tasks code generator.
 */
abstract class SimpleBakeTask extends BakeTask
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
     * @return array
     * @phpstan-return array<string, mixed>
     */
    public function templateData(): array
    {
        $namespace = Configure::read('App.namespace');
        if ($this->plugin) {
            $namespace = $this->_pluginNamespace($this->plugin);
        }

        return ['namespace' => $namespace];
    }

    /**
     * Execute method
     *
     * @param string|null $name The name of the object to bake.
     * @return int|null
     */
    public function main(?string $name = null): ?int
    {
        parent::main();
        if (empty($name)) {
            $this->abort('You must provide a name to bake a ' . $this->name());
        }
        $name = $this->_getName($name);
        $name = Inflector::camelize($name);
        $this->bake($name);

        return static::CODE_SUCCESS;
    }

    /**
     * Generate a class stub
     *
     * @param string $name The classname to generate.
     * @return string
     */
    public function bake(string $name): string
    {
        $renderer = new TemplateRenderer($this->param('theme'));
        $renderer->set('name', $name);
        $renderer->set($this->templateData());
        $contents = $renderer->generate($this->template());

        $filename = $this->getPath() . $this->fileName($name);
        $this->createFile($filename, $contents);
        $emptyFile = $this->getPath() . '.gitkeep';
        $this->_deleteEmptyFile($emptyFile);

        return $contents;
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser(): ConsoleOptionParser
    {
        $parser = parent::getOptionParser();
        $name = $this->name();
        $parser->setDescription(
            sprintf('Bake a %s class file.', $name)
        )->addArgument('name', [
            'help' => sprintf(
                'Name of the %s to bake. Can use Plugin.name to bake %s files into plugins.',
                $name,
                $name
            ),
        ]);

        return $parser;
    }
}
