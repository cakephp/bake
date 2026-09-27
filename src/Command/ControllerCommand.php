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

use Bake\Utility\TableScanner;
use Cake\Console\Arguments;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;

/**
 * Task class for creating and updating controller files.
 */
class ControllerCommand extends BakeCommand
{
    /**
     * Path fragment for generated code.
     */
    public string $pathFragment = 'Controller/';

    /**
     * @inheritDoc
     */
    public static function getDescription(): string
    {
        return 'Create a controller and test';
    }

    /**
     * Execute the command.
     *
     * @return int|null The exit code or null for success
     */
    public function execute(): ?int
    {
        $this->extractCommonProperties($this->args);
        $name = $this->args->getArgument('name') ?? '';
        $name = $this->getNameWithoutPrefix($name);
        if (empty($name)) {
            /** @var \Cake\Database\Connection $connection */
            $connection = ConnectionManager::get($this->connection);
            $scanner = new TableScanner($connection);
            $this->io->out('Possible controllers based on your current database:');
            foreach ($scanner->listUnskipped() as $table) {
                $this->io->out('- ' . $this->camelize($table));
            }

            return static::CODE_SUCCESS;
        }
        $controller = $this->camelize($name);
        $this->bake($controller);

        return static::CODE_SUCCESS;
    }

    /**
     * Assembles and writes a Controller file
     *
     * @param string $controllerName Controller name already pluralized and correctly cased.
     * @return void
     */
    public function bake(string $controllerName): void
    {
        $this->io->quiet(sprintf('Baking controller class for %s...', $controllerName));

        $actions = [];
        if (!$this->args->getOption('no-actions') && !$this->args->getOption('actions')) {
            $actions = ['index', 'view', 'add', 'edit', 'delete'];
        }
        if ($this->args->getOption('actions')) {
            $actions = array_map('trim', explode(',', (string)$this->args->getOption('actions')));
            $actions = array_filter($actions);
        }
        if (!$this->args->getOption('actions') && Plugin::isLoaded('Authentication') && $controllerName === 'Users') {
            $actions[] = 'login';
        }

        $helpers = $this->getHelpers();
        $components = $this->getComponents();

        $prefix = $this->getPrefix();
        if ($prefix) {
            $prefix = '\\' . str_replace('/', '\\', $prefix);
        }
        // Controllers default to importing AppController from `App`
        $baseNamespace = Configure::read('App.namespace');
        $namespace = $baseNamespace;
        if ($this->plugin) {
            $namespace = $this->pluginNamespace($this->plugin);
        }
        // If the plugin has an AppController other plugin controllers
        // should inherit from it.
        if ($this->plugin && class_exists("{$namespace}\Controller\AppController")) {
            $baseNamespace = $namespace;
        }

        $currentModelName = $controllerName;
        $plugin = $this->plugin;
        $pluginPath = $plugin;
        if ($pluginPath) {
            $pluginPath .= '.';
        }

        if ($this->getTableLocator()->exists($pluginPath . $currentModelName)) {
            $modelObj = $this->getTableLocator()->get($pluginPath . $currentModelName);
        } else {
            $modelObj = $this->getTableLocator()->get($pluginPath . $currentModelName, [
                'connectionName' => $this->connection,
            ]);
        }

        $pluralName = $this->variableName($currentModelName);
        $singularName = $this->singularName($currentModelName);
        $singularHumanName = $this->singularHumanName($controllerName);
        $pluralHumanName = $this->variableName($controllerName);

        // Handle cases where singular and plural are identical (e.g., "news", "sheep")
        // to avoid variable collisions in generated controller code
        if ($singularName === $pluralName) {
            $singularName .= 'Entity';
        }

        $defaultModel = sprintf('%s\Model\Table\%sTable', $namespace, $controllerName);
        if (!class_exists($defaultModel)) {
            $defaultModel = null;
        }
        $entityClassName = $this->entityName($modelObj->getAlias());

        $data = compact(
            'actions',
            'components',
            'currentModelName',
            'defaultModel',
            'entityClassName',
            'helpers',
            'modelObj',
            'namespace',
            'baseNamespace',
            'plugin',
            'pluralHumanName',
            'pluralName',
            'prefix',
            'singularHumanName',
            'singularName',
        );
        $data['name'] = $controllerName;

        $this->bakeController($controllerName, $data);
        $this->bakeTest($controllerName);
    }

    /**
     * Generate the controller code
     *
     * @param string $controllerName The name of the controller.
     * @param array<string, mixed> $data The data to turn into code.
     * @return void
     */
    public function bakeController(string $controllerName, array $data): void
    {
        $data += [
            'name' => null,
            'namespace' => null,
            'prefix' => null,
            'actions' => null,
            'helpers' => null,
            'components' => null,
            'plugin' => null,
            'pluginPath' => null,
        ];

        $contents = $this->createTemplateRenderer()
            ->set($data)
            ->generate('Bake.Controller/controller');

        $path = $this->getPath();
        $filename = $path . $controllerName . 'Controller.php';
        $this->io->createFile($filename, $contents, $this->force);
    }

    /**
     * Assembles and writes a unit test file
     *
     * @param string $className Controller class name
     * @return void
     */
    public function bakeTest(string $className): void
    {
        if ($this->args->getOption('no-test')) {
            return;
        }
        $test = new TestCommand();
        $testArgs = new Arguments(
            ['controller', $className],
            $this->args->getOptions(),
            ['type', 'name'],
        );
        $test->setArgs($testArgs);
        $test->setIo($this->io);
        $test->execute();
    }

    /**
     * Get the list of components for the controller.
     *
     * @return array<string>
     */
    public function getComponents(): array
    {
        $components = [];
        if ($this->args->getOption('components')) {
            $components = explode(',', (string)$this->args->getOption('components'));
            $components = array_values(array_filter(array_map('trim', $components)));
        } elseif (Plugin::isLoaded('Authorization')) {
            $components[] = 'Authorization.Authorization';
        }

        return $components;
    }

    /**
     * Get the list of helpers for the controller.
     *
     * @return array<string>
     */
    public function getHelpers(): array
    {
        $helpers = [];
        if ($this->args->getOption('helpers')) {
            $helpers = explode(',', (string)$this->args->getOption('helpers'));
            $helpers = array_values(array_filter(array_map('trim', $helpers)));
        }

        return $helpers;
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The console option parser
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = $this->setCommonOptions($parser);
        $parser->setDescription(
            'Bake a controller skeleton.',
        )->addArgument('name', [
            'help' => 'Name of the controller to bake (without the `Controller` suffix). ' .
                'You can use Plugin.name to bake controllers into plugins.',
        ])->addOption('components', [
            'help' => 'The comma separated list of components to use.',
        ])->addOption('helpers', [
            'help' => 'The comma separated list of helpers to use.',
        ])->addOption('prefix', [
            'help' => 'The namespace/routing prefix to use.',
        ])->addOption('actions', [
            'help' => 'The comma separated list of actions to generate. ' .
                      'You can include custom methods provided by your template set here.',
        ])->addOption('no-test', [
            'boolean' => true,
            'help' => 'Do not generate a test skeleton.',
        ])->addOption('no-actions', [
            'boolean' => true,
            'help' => 'Do not generate basic CRUD action methods.',
        ]);

        return $parser;
    }
}
