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
namespace Bake\Command;

use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;

/**
 * Task for creating cells.
 */
class CellCommand extends SimpleBakeCommand
{
    /**
     * Task name used in path generation.
     */
    public string $pathFragment = 'View/Cell/';

    /**
     * @inheritDoc
     */
    public static function getDescription(): string
    {
        return 'Create a view cell, template and test.';
    }

    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'cell';
    }

    /**
     * @inheritDoc
     */
    public function fileName(string $name): string
    {
        return $name . 'Cell.php';
    }

    /**
     * @inheritDoc
     */
    public function template(): string
    {
        return 'Bake.View/cell';
    }

    /**
     * Get template data.
     *
     * @return array<string, mixed>
     */
    public function templateData(): array
    {
        $prefix = $this->getPrefix();
        if ($prefix) {
            $prefix = '\\' . str_replace('/', '\\', $prefix);
        }

        $namespace = Configure::read('App.namespace');
        if ($this->plugin) {
            $namespace = $this->pluginNamespace($this->plugin);
        }

        return compact('namespace', 'prefix');
    }

    /**
     * Bake the Cell class and template file.
     *
     * @param string $name The name of the cell to make.
     * @return void
     */
    protected function bake(string $name): void
    {
        $this->bakeTemplate($name);

        parent::bake($name);
    }

    /**
     * Bake an empty file for a cell.
     *
     * @param string $name The name of the cell a template is needed for.
     * @return void
     */
    protected function bakeTemplate(string $name): void
    {
        $path = $this->getTemplatePath('cell');
        $path .= implode(DS, [$name, 'display.php']);

        $this->io->createFile($path, '', $this->force);
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser Parser instance
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->addOption('prefix', [
            'help' => 'The namespace prefix to use.',
        ]);

        return $parser;
    }
}
