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
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Shell\Task;

use Bake\Utility\Model\AssociationFilter;
use Cake\Console\Shell;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

/**
 * Task class for creating and updating view template files.
 *
 */
class TemplateTask extends BakeTask
{
    /**
     * Tasks to be loaded by this Task
     *
     * @var array
     */
    public $tasks = [
        'Bake.Model',
        'Bake.BakeTemplate'
    ];

    /**
     * path to Template directory
     *
     * @var array
     */
    public $pathFragment = 'Template/';

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
     * The template file to use
     *
     * @var string
     */
    public $template = null;

    /**
     * Actions to use for scaffolding
     *
     * @var array
     */
    public $scaffoldActions = ['index', 'view', 'add', 'edit'];

    /**
     * An array of action names that don't require templates. These
     * actions will not emit errors when doing bakeActions()
     *
     * @var array
     */
    public $noTemplateActions = ['delete'];

    /**
     * AssociationFilter utility
     *
     * @var AssociationFilter
     */
    protected $_associationFilter = null;

    /**
     * Override initialize
     *
     * @return void
     */
    public function initialize()
    {
        $this->path = current(App::path('Template'));
    }

    /**
     * Execution method always used for tasks
     *
     * @param string|null $name The name of the controller to bake view templates for.
     * @param string|null $template The template to bake with.
     * @param string|null $action The action to bake with.
     * @return mixed
     */
    public function main($name = null, $template = null, $action = null)
    {
        parent::main();

        if (empty($name)) {
            $this->out('Possible tables to bake view templates for based on your current database:');
            $this->Model->connection = $this->connection;
            foreach ($this->Model->listUnskipped() as $table) {
                $this->out('- ' . $this->_camelize($table));
            }
            return true;
        }
        $name = $this->_getName($name);

        $controller = null;
        if (!empty($this->params['controller'])) {
            $controller = $this->params['controller'];
        }
        $this->controller($name, $controller);
        $this->model($name);

        if (isset($template)) {
            $this->template = $template;
        }
        if (!$action) {
            $action = $this->template;
        }
        if ($action) {
            return $this->bake($action, true);
        }

        $vars = $this->_loadController();
        $methods = $this->_methodsToBake();

        foreach ($methods as $method) {
            $content = $this->getContent($method, $vars);
            if ($content) {
                $this->bake($method, $content);
            }
        }
    }

    /**
     * Set the model class for the table.
     *
     * @param string $table The table/model that is being baked.
     * @return void
     */
    public function model($table)
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
    public function controller($table, $controller = null)
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
        $this->controllerClass = App::className($plugin . $prefix . $controller, 'Controller', 'Controller');
    }

    /**
     * Get the path base for view templates.
     *
     * @return string
     */
    public function getPath()
    {
        $path = parent::getPath();
        $path .= $this->controllerName . DS;
        return $path;
    }

    /**
     * Get a list of actions that can / should have view templates baked for them.
     *
     * @return array Array of action names that should be baked
     */
    protected function _methodsToBake()
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
     * Bake All view templates for all controller actions.
     *
     * @return void
     */
    public function all()
    {
        $this->Model->connection = $this->connection;
        $tables = $this->Model->listUnskipped();

        foreach ($tables as $table) {
            $this->main($table);
        }
    }

    /**
     * Loads Controller and sets variables for the template
     * Available template variables:
     *
     * - 'modelObject'
     * - 'modelClass'
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
    protected function _loadController()
    {
        if (TableRegistry::exists($this->modelName)) {
            $modelObject = TableRegistry::get($this->modelName);
        } else {
            $modelObject = TableRegistry::get($this->modelName, [
                'connectionName' => $this->connection
            ]);
        }

        $primaryKey = (array)$modelObject->primaryKey();
        $displayField = $modelObject->displayField();
        $singularVar = $this->_singularName($this->controllerName);
        $singularHumanName = $this->_singularHumanName($this->controllerName);
        $schema = $modelObject->schema();
        $fields = $schema->columns();
        $modelClass = $this->modelName;
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
            'schema',
            'primaryKey',
            'displayField',
            'singularVar',
            'pluralVar',
            'singularHumanName',
            'pluralHumanName',
            'fields',
            'associations',
            'keyFields'
        );
    }

    /**
     * Bake a view file for each of the supplied actions
     *
     * @param array $actions Array of actions to make files for.
     * @param array $vars The context for generating views.
     * @return void
     */
    public function bakeActions(array $actions, $vars)
    {
        foreach ($actions as $action) {
            $content = $this->getContent($action, $vars);
            $this->bake($action, $content);
        }
    }

    /**
     * handle creation of baking a custom action view file
     *
     * @return void
     */
    public function customAction()
    {
        $action = '';
        while (!$action) {
            $action = $this->in('Action Name? (use lowercase_underscored function name)');
            if (!$action) {
                $this->out('The action name you supplied was empty. Please try again.');
            }
        }

        $path = $this->getPath() . $this->controllerName . DS . Inflector::underscore($action) . ".ctp";

        $this->out();
        $this->hr();
        $this->out('The following view will be created:');
        $this->hr();
        $this->out(sprintf('Controller Name: %s', $this->controllerName));
        $this->out(sprintf('Action Name:     %s', $action));
        $this->out(sprintf('Path:            %s', $path));
        $this->hr();
        $looksGood = $this->in('Look okay?', ['y', 'n'], 'y');
        if (strtolower($looksGood) === 'y') {
            $this->bake($action, ' ');
            return $this->_stop();
        }
        $this->out('Bake Aborted.');
    }

    /**
     * Assembles and writes bakes the view file.
     *
     * @param string $action Action to bake.
     * @param string $content Content to write.
     * @return string Generated file content.
     */
    public function bake($action, $content = '')
    {
        if ($content === true) {
            $content = $this->getContent($action);
        }
        if (empty($content)) {
            $this->err("<warning>No generated content for '{$action}.ctp', not generating template.</warning>");
            return false;
        }
        $this->out("\n" . sprintf('Baking `%s` view template file...', $action), 1, Shell::QUIET);
        $path = $this->getPath();
        $filename = $path . Inflector::underscore($action) . '.ctp';
        $this->createFile($filename, $content);
        return $content;
    }

    /**
     * Builds content from template and variables
     *
     * @param string $action name to generate content to
     * @param array|null $vars passed for use in templates
     * @return string content from template
     */
    public function getContent($action, $vars = null)
    {
        if (!$vars) {
            $vars = $this->_loadController();
        }

        if (empty($vars['primaryKey'])) {
            $this->error('Cannot generate views for models with no primary key');
            return false;
        }

        if ($action === "index" && !empty($this->params['index-columns'])) {
            $this->BakeTemplate->set('indexColumns', $this->params['index-columns']);
        }

        $this->BakeTemplate->set('action', $action);
        $this->BakeTemplate->set('plugin', $this->plugin);
        $this->BakeTemplate->set($vars);

        return $this->BakeTemplate->generate("Template/$action");
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->description(
            'Bake views for a controller, using built-in or custom templates. '
        )->addArgument('controller', [
            'help' => 'Name of the controller views to bake. Can be Plugin.name as a shortcut for plugin baking.'
        ])->addArgument('action', [
            'help' => "Will bake a single action's file. core templates are (index, add, edit, view)"
        ])->addArgument('alias', [
            'help' => 'Will bake the template in <action> but create the filename after <alias>.'
        ])->addOption('controller', [
            'help' => 'The controller name if you have a controller that does not follow conventions.'
        ])->addOption('prefix', [
            'help' => 'The routing prefix to generate views for.',
        ])->addOption('index-columns', [
            'help' => 'Limit for the number of index columns',
            'default' => 0
        ])->addSubcommand('all', [
            'help' => '[optional] Bake all CRUD action views for all controllers. Requires models and controllers to exist.'
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
    protected function _filteredAssociations(Table $model)
    {
        if (is_null($this->_associationFilter)) {
            $this->_associationFilter = new AssociationFilter();
        }
        return $this->_associationFilter->filterAssociations($model);
    }
}
