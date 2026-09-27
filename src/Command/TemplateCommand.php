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

use Bake\Utility\Model\AssociationFilter;
use Bake\Utility\TableScanner;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use Cake\View\Exception\MissingTemplateException;
use Exception;
use RuntimeException;
use function Cake\Core\namespaceSplit;

/**
 * Task class for creating view template files.
 */
class TemplateCommand extends BakeCommand
{
    /**
     * Name of the controller being used
     */
    public string $controllerName;

    /**
     * Classname of the controller being used
     */
    public string $controllerClass;

    /**
     * Name with plugin of the model being used
     */
    public string $modelName;

    /**
     * Actions to use for scaffolding
     *
     * @var array<string>
     */
    public array $scaffoldActions = ['index', 'view', 'add', 'edit'];

    /**
     * Actions that exclude hidden fields
     *
     * @var array<string>
     */
    public array $excludeHiddenActions = ['index', 'view'];

    /**
     * AssociationFilter utility
     */
    protected ?AssociationFilter $associationFilter = null;

    /**
     * Template path.
     */
    public string $path;

    /**
     * Output extension
     */
    public string $ext = 'php';

    /**
     * @inheritDoc
     */
    public static function getDescription(): string
    {
        return 'Create a view template.';
    }

    /**
     * Override initialize
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $templatePaths = App::path('templates');
        if ($templatePaths === []) {
            throw new RuntimeException(
                'Could not read template paths. ' .
                'Ensure `App.paths.templates` is defined in your application configuration.',
            );
        }

        $this->path = current($templatePaths);
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
            $this->io->out('Possible tables to bake view templates for based on your current database:');
            /** @var \Cake\Database\Connection $connection */
            $connection = ConnectionManager::get($this->connection);
            $scanner = new TableScanner($connection);
            foreach ($scanner->listUnskipped() as $table) {
                $this->io->out('- ' . $this->camelize($table));
            }

            return static::CODE_SUCCESS;
        }
        $template = $this->args->getArgument('template');
        $action = $this->args->getArgument('action');
        $this->controller($name, (string)$this->args->getOption('controller'));
        $this->model($name);
        if ($template && $action === null) {
            $action = $template;
        }
        if ($template) {
            $this->bake($template, true, $action);

            return static::CODE_SUCCESS;
        }
        $vars = $this->loadController();
        $methods = $this->methodsToBake();
        foreach ($methods as $method) {
            try {
                $content = $this->getContent($method, $vars);
                $this->bake($method, $content);
            } catch (MissingTemplateException $e) {
                $this->io->verbose($e->getMessage());
            } catch (RuntimeException $e) {
                $this->io->error($e->getMessage());
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
        $tableName = $this->camelize($table);
        $plugin = $this->plugin;
        if ($plugin) {
            $plugin .= '.';
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
        $tableName = $this->camelize($table);
        if (empty($controller)) {
            $controller = $tableName;
        }
        $this->controllerName = $controller;

        $plugin = $this->plugin;
        if ($plugin) {
            $plugin .= '.';
        }
        $prefix = $this->getPrefix();
        if ($prefix) {
            $prefix .= '/';
        }
        $this->controllerClass = (string)App::className($plugin . $prefix . $controller, 'Controller', 'Controller');
    }

    /**
     * Get the path base for view templates.
     *
     * @param string|null $container Unused.
     * @return string
     */
    public function getTemplatePath(?string $container = null): string
    {
        $path = parent::getTemplatePath($container);

        return $path . $this->controllerName . DS;
    }

    /**
     * Get a list of actions that can / should have view templates baked for them.
     *
     * @return array<string> Array of action names that should be baked
     */
    protected function methodsToBake(): array
    {
        $base = Configure::read('App.namespace');

        $methods = [];
        if (class_exists($this->controllerClass)) {
            $methods = array_diff(
                array_map(
                    'Cake\Utility\Inflector::underscore',
                    get_class_methods($this->controllerClass),
                ),
                array_map(
                    'Cake\Utility\Inflector::underscore',
                    get_class_methods($base . '\Controller\AppController'),
                ),
            );
        }
        if ($methods === []) {
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
     * @return array<string, mixed> Returns variables to be made available to a view template
     */
    protected function loadController(): array
    {
        if ($this->getTableLocator()->exists($this->modelName)) {
            $modelObject = $this->getTableLocator()->get($this->modelName);
        } else {
            $modelObject = $this->getTableLocator()->get($this->modelName, [
                'connectionName' => $this->connection,
            ]);
        }
        $namespace = Configure::read('App.namespace');
        $primaryKey = null;
        $displayField = null;
        $singularVar = null;
        $singularHumanName = null;
        $schema = null;
        $fields = null;
        $hidden = null;
        $modelClass = null;
        try {
            $primaryKey = (array)$modelObject->getPrimaryKey();
            $displayField = $modelObject->getDisplayField();
            $singularVar = $this->singularName($this->controllerName);
            $singularHumanName = $this->singularHumanName($this->controllerName);
            $schema = $modelObject->getSchema();
            $fields = $schema->columns();
            $hidden = $modelObject->newEmptyEntity()->getHidden() ?: ['token', 'password', 'passwd'];
            $modelClass = $this->modelName;
        } catch (Exception $exception) {
            $this->io->error($exception->getMessage());
            $this->abort();
        }
        [, $entityClass] = namespaceSplit($this->entityName($this->modelName));
        $entityClass = sprintf('%s\Model\Entity\%s', $namespace, $entityClass);
        if (!class_exists($entityClass)) {
            $entityClass = EntityInterface::class;
        }
        $associations = $this->filteredAssociations($modelObject);
        $keyFields = [];
        if (isset($associations['BelongsToMany'])) {
            foreach ($associations['BelongsToMany'] as $assoc) {
                $keyFields[$assoc['foreignKey']] = $assoc['variable'];
            }
        }
        if (isset($associations['BelongsTo'])) {
            foreach ($associations['BelongsTo'] as $assoc) {
                $keyFields[$assoc['foreignKey']] = $assoc['variable'];
            }
        }
        $pluralVar = Inflector::variable($this->controllerName);
        $pluralHumanName = $this->pluralHumanName($this->controllerName);
        // Handle cases where singular and plural are identical (e.g., "news", "sheep")
        // to avoid generating invalid code like `foreach ($news as $news)`
        if ($singularVar === $pluralVar) {
            $singularVar .= 'Entity';
        }

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
            'hidden',
            'associations',
            'keyFields',
            'namespace',
        );
    }

    /**
     * Assembles and writes bakes the view file.
     *
     * @param string $template Template file to use.
     * @param string|true $content Content to write.
     * @param ?string $outputFile The output file to create. If null will use `$template`
     * @return void
     */
    public function bake(
        string $template,
        string|bool $content = '',
        ?string $outputFile = null,
    ): void {
        $outputFile ??= $template;
        if ($content === true) {
            $content = $this->getContent($template);
        }
        if (empty($content)) {
            // phpcs:ignore Generic.Files.LineLength
            $this->io->warning("No generated content for '{$template}.{$this->ext}', not generating template.");

            return;
        }
        $path = $this->getTemplatePath();
        $filename = $path . Inflector::underscore($outputFile) . '.' . $this->ext;

        $this->io->out("\n" . sprintf('Baking `%s` view template file...', $outputFile));
        $this->io->createFile($filename, $content, $this->force);
    }

    /**
     * Builds content from template and variables
     *
     * @param string $action name to generate content to
     * @param array<string, mixed>|null $vars passed for use in templates
     * @return string Content from template
     */
    public function getContent(string $action, ?array $vars = null): string
    {
        if (!$vars) {
            $vars = $this->loadController();
        }

        if (empty($vars['primaryKey'])) {
            $this->io->error('Cannot generate views for models with no primary key');
            $this->abort();
        }

        if (in_array($action, $this->excludeHiddenActions)) {
            $vars['fields'] = array_diff($vars['fields'], $vars['hidden']);
        }

        $renderer = $this->createTemplateRenderer()
            ->set('action', $action)
            ->set('plugin', $this->plugin)
            ->set($vars);

        $indexColumns = 0;
        if ($action === 'index' && $this->args->getOption('index-columns') !== null) {
            $indexColumns = $this->args->getOption('index-columns');
        }
        $renderer->set('indexColumns', $indexColumns);

        // Always use domain translations when in plugin context
        $useDomain = (bool)$this->plugin;
        $renderer->set('useDomain', $useDomain);

        return $renderer->generate("Bake.Template/{$action}");
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The option parser to update.
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = $this->setCommonOptions($parser);

        $parser->setDescription(
            'Bake views for a controller, using built-in or custom templates. ',
        )->addArgument('name', [
            'help' => 'Name of the controller views to bake. You can use Plugin.name as a shortcut for plugin baking.',
        ])->addArgument('template', [
            'help' => "Will bake a single action's file. core templates are (index, add, edit, view)",
        ])->addArgument('action', [
            'help' => 'Will bake the template in <template> but create the filename named <action>.',
        ])->addOption('controller', [
            'help' => 'The controller name if you have a controller that does not follow conventions.',
        ])->addOption('prefix', [
            'help' => 'The routing prefix to generate views for.',
        ])->addOption('index-columns', [
            'help' => 'Limit for the number of index columns',
            'default' => '0',
        ]);

        return $parser;
    }

    /**
     * Get filtered associations
     * To be mocked...
     *
     * @param \Cake\ORM\Table $model Table
     * @return array<string, array<string, mixed>> associations
     */
    protected function filteredAssociations(Table $model): array
    {
        if (!$this->associationFilter instanceof AssociationFilter) {
            $this->associationFilter = new AssociationFilter();
        }

        return $this->associationFilter->filterAssociations($model);
    }
}
