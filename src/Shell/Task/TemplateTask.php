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
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Shell\Task;

use Bake\Utility\Model\AssociationFilter;
use Bake\Utility\TableScanner;
use Bake\Utility\TemplateRenderer;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\View\Exception\MissingTemplateException;
use RuntimeException;

/**
 * Task class for creating and updating view template files.
 */
class TemplateTask extends BakeTask
{
    /**
     * path to Template directory
     *
     * @var string
     */
    public $pathFragment = '../templates/';

    /**
     * Name of the controller being used
     *
     * @var string
     */
    public $controllerName = null;

    /**
     * Classname of the controller being used
     *
     * @var string
     */
    public $controllerClass = null;

    /**
     * Name with plugin of the model being used
     *
     * @var string
     */
    public $modelName = null;

    /**
     * Actions to use for scaffolding
     *
     * @var string[]
     */
    public $scaffoldActions = ['index', 'view', 'add', 'edit'];

    /**
     * AssociationFilter utility
     *
     * @var \Bake\Utility\Model\AssociationFilter|null
     */
    protected $_associationFilter = null;

    /**
     * Template path.
     *
     * @var string
     */
    public $path;

    /**
     * Override initialize
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->path = current(App::path('Template'));
    }

    /**
     * Execution method always used for tasks
     *
     * @return null|int
     */
    public function main(): ?int
    {
        parent::main();
        [$name, $template, $action] = $this->args + [null, null, null];

        if (empty($name)) {
            $this->out('Possible tables to bake view templates for based on your current database:');

            $scanner = new TableScanner(ConnectionManager::get($this->connection));
            foreach ($scanner->listUnskipped() as $table) {
                $this->out('- ' . $this->_camelize($table));
            }

            return static::CODE_SUCCESS;
        }
        $name = $this->_getName($name);

        $controller = null;
        if (!empty($this->params['controller'])) {
            $controller = $this->params['controller'];
        }
        $this->controller($name, $controller);
        $this->model($name);

        if ($template && $action === null) {
            $action = $template;
        }
        if ($template) {
            $this->bake($template, true, $action);

            return static::CODE_SUCCESS;
        }

        $vars = $this->_loadController();
        $methods = $this->_methodsToBake();

        foreach ($methods as $method) {
            try {
                $content = $this->getContent($method, $vars);
                $this->bake($method, $content);
            } catch (MissingTemplateException $e) {
                $this->_io->verbose($e->getMessage());
            } catch (RuntimeException $e) {
                $this->_io->err($e->getMessage());
            }
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Set the model class for the table.
     *
     * @param string $table The table/model that is being baked.
     * @return void
     */
    public function model(string $table): void
    {
        $tableName = $this->_camelize($table);
        $plugin = null;
        if (!empty($this->params['plugin'])) {
            $plugin = $this->params['plugin'] . '.';
        }
        $this->modelName = $plugin . $tableName;
    }

    /**
     * Set the controller related properties.
     *
     * @param string $table The table/model that is being baked.
     * @param string|null $controller The controller name if specified.
     * @return void
     */
    public function controller(string $table, ?string $controller = null): void
    {
        $tableName = $this->_camelize($table);
        if (empty($controller)) {
            $controller = $tableName;
        }
        $this->controllerName = $controller;

        $plugin = $this->param('plugin');
        if ($plugin) {
            $plugin .= '.';
        }
        $prefix = $this->_getPrefix();
        if ($prefix) {
            $prefix .= '/';
        }
        $this->controllerClass = (string)App::className($plugin . $prefix . $controller, 'Controller', 'Controller');
    }

    /**
     * Get the path base for view templates.
     *
     * @return string
     */
    public function getPath(): string
    {
        $path = parent::getPath();
        $path .= $this->controllerName . DS;

        return $path;
    }

    /**
     * Get a list of actions that can / should have view templates baked for them.
     *
     * @return string[] Array of action names that should be baked
     */
    protected function _methodsToBake(): array
    {
        $base = Configure::read('App.namespace');

        $methods = [];
        if (class_exists($this->controllerClass)) {
            $methods = array_diff(
                array_map(
                    'Cake\Utility\Inflector::underscore',
                    get_class_methods($this->controllerClass)
                ),
                array_map(
                    'Cake\Utility\Inflector::underscore',
                    get_class_methods($base . '\Controller\AppController')
                )
            );
        }
        if (empty($methods)) {
            $methods = $this->scaffoldActions;
        }
        foreach ($methods as $i => $method) {
            if ($method[0] === '_') {
                unset($methods[$i]);
            }
        }

        return $methods;
    }

    /**
     * Loads Controller and sets variables for the template
     * Available template variables:
     *
     * - 'modelObject'
     * - 'modelClass'
     * - 'entityClass'
     * - 'primaryKey'
     * - 'displayField'
     * - 'singularVar'
     * - 'pluralVar'
     * - 'singularHumanName'
     * - 'pluralHumanName'
     * - 'fields'
     * - 'keyFields'
     * - 'schema'
     *
     * @return array Returns variables to be made available to a view template
     */
    protected function _loadController(): array
    {
        if (TableRegistry::getTableLocator()->exists($this->modelName)) {
            $modelObject = TableRegistry::getTableLocator()->get($this->modelName);
        } else {
            $modelObject = TableRegistry::getTableLocator()->get($this->modelName, [
                'connectionName' => $this->connection,
            ]);
        }

        $namespace = Configure::read('App.namespace');

        $primaryKey = (array)$modelObject->getPrimaryKey();
        $displayField = $modelObject->getDisplayField();
        $singularVar = $this->_singularName($this->controllerName);
        $singularHumanName = $this->_singularHumanName($this->controllerName);
        $schema = $modelObject->getSchema();
        $fields = $schema->columns();
        $modelClass = $this->modelName;

        [, $entityClass] = namespaceSplit($this->_entityName($this->modelName));
        $entityClass = sprintf('%s\Model\Entity\%s', $namespace, $entityClass);
        if (!class_exists($entityClass)) {
            $entityClass = EntityInterface::class;
        }
        $associations = $this->_filteredAssociations($modelObject);
        $keyFields = [];
        if (!empty($associations['BelongsTo'])) {
            foreach ($associations['BelongsTo'] as $assoc) {
                $keyFields[$assoc['foreignKey']] = $assoc['variable'];
            }
        }

        $pluralVar = Inflector::variable($this->controllerName);
        $pluralHumanName = $this->_pluralHumanName($this->controllerName);

        return compact(
            'modelObject',
            'modelClass',
            'entityClass',
            'schema',
            'primaryKey',
            'displayField',
            'singularVar',
            'pluralVar',
            'singularHumanName',
            'pluralHumanName',
            'fields',
            'associations',
            'keyFields',
            'namespace'
        );
    }

    /**
     * Assembles and writes bakes the view file.
     *
     * @param string $template Template file to use.
     * @param string|true $content Content to write.
     * @param string $outputFile The output file to create. If null will use `$template`
     * @return string|false Generated file content.
     */
    public function bake(string $template, $content = '', $outputFile = null)
    {
        if ($outputFile === null) {
            $outputFile = $template;
        }
        if ($content === true) {
            $content = $this->getContent($template);
        }
        if (empty($content)) {
            $this->err("<warning>No generated content for '{$template}.php', not generating template.</warning>");

            return false;
        }
        $this->out("\n" . sprintf('Baking `%s` view template file...', $outputFile), 1, Shell::QUIET);
        $path = $this->getPath();
        $filename = $path . Inflector::underscore($outputFile) . '.php';
        $this->createFile($filename, $content);

        return $content;
    }

    /**
     * Builds content from template and variables
     *
     * @param string $action name to generate content to
     * @param array|null $vars passed for use in templates
     * @return string|false Content from template
     */
    public function getContent(string $action, ?array $vars = null)
    {
        if (!$vars) {
            $vars = $this->_loadController();
        }

        if (empty($vars['primaryKey'])) {
            $this->abort('Cannot generate views for models with no primary key');

            return false;
        }

        $renderer = new TemplateRenderer($this->param('theme'));
        if ($action === "index" && !empty($this->params['index-columns'])) {
            $renderer->set('indexColumns', $this->params['index-columns']);
        }

        $renderer->set('action', $action);
        $renderer->set('plugin', $this->plugin);
        $renderer->set($vars);

        return $renderer->generate("Template/$action");
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser(): ConsoleOptionParser
    {
        $parser = parent::getOptionParser();

        $parser->setDescription(
            'Bake views for a controller, using built-in or custom templates. '
        )->addArgument('controller', [
            'help' => 'Name of the controller views to bake. You can use Plugin.name as a shortcut for plugin baking.',
        ])->addArgument('action', [
            'help' => "Will bake a single action's file. core templates are (index, add, edit, view)",
        ])->addArgument('alias', [
            'help' => 'Will bake the template in <action> but create the filename after <alias>.',
        ])->addOption('controller', [
            'help' => 'The controller name if you have a controller that does not follow conventions.',
        ])->addOption('prefix', [
            'help' => 'The routing prefix to generate views for.',
        ])->addOption('index-columns', [
            'help' => 'Limit for the number of index columns',
            'default' => 0,
        ])->addSubcommand('all', [
            'help' => '[optional] Bake all CRUD action views for all controllers.' .
                'Requires models and controllers to exist.',
        ]);

        return $parser;
    }

    /**
     * Get filtered associations
     * To be mocked...
     *
     * @param \Cake\ORM\Table $model Table
     * @return array associations
     */
    protected function _filteredAssociations(Table $model): array
    {
        if (is_null($this->_associationFilter)) {
            $this->_associationFilter = new AssociationFilter();
        }

        return $this->_associationFilter->filterAssociations($model);
    }
}
