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
namespace Bake\Command;

use Bake\Utility\TableScanner;
use Bake\Utility\TemplateRenderer;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Exception\StopException;
use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Database\Exception;
use Cake\Database\Schema\CachedCollection;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Table;
use Cake\Utility\Inflector;

/**
 * Command for generating model files.
 */
class ModelCommand extends BakeCommand
{
    /**
     * path to Model directory
     *
     * @var string
     */
    public $pathFragment = 'Model/';

    /**
     * Table prefix
     *
     * Can be replaced in application subclasses if necessary
     *
     * @var string
     */
    public $tablePrefix = '';

    /**
     * Holds tables found on connection.
     *
     * @var string[]
     */
    protected $_tables = [];

    /**
     * Execute the command.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->extractCommonProperties($args);
        $name = $this->_getName($args->getArgument('name') ?? '');

        if (empty($name)) {
            $io->out('Choose a model to bake from the following:');
            foreach ($this->listUnskipped() as $table) {
                $io->out('- ' . $this->_camelize($table));
            }

            return static::CODE_SUCCESS;
        }

        // Disable caching before baking with connection
        $connection = ConnectionManager::get($this->connection);
        if ($connection instanceof Connection) {
            $collection = $connection->getSchemaCollection();
            if ($collection instanceof CachedCollection) {
                $connection->getCacher()->clear();
                $connection->cacheMetadata(false);
            }
        }

        $this->bake($this->_camelize($name), $args, $io);

        return static::CODE_SUCCESS;
    }

    /**
     * Generate code for the given model name.
     *
     * @param string $name The model name to generate.
     * @param \Cake\Console\Arguments $args Console Arguments.
     * @param \Cake\Console\ConsoleIo $io Console Io.
     * @return void
     */
    public function bake(string $name, Arguments $args, ConsoleIo $io): void
    {
        $table = $this->getTable($name, $args);
        $tableObject = $this->getTableObject($name, $table);
        $this->validateNames($tableObject->getSchema());
        $data = $this->getTableContext($tableObject, $table, $name, $args, $io);
        $this->bakeTable($tableObject, $data, $args, $io);
        $this->bakeEntity($tableObject, $data, $args, $io);
        $this->bakeFixture($tableObject->getAlias(), $tableObject->getTable(), $args, $io);
        $this->bakeTest($tableObject->getAlias(), $args, $io);
    }

    /**
     * Validates table and column names are supported.
     *
     * @param \Cake\Database\Schema\TableSchemaInterface $schema Table schema
     * @return void
     * @throws \Cake\Console\Exception\StopException When table or column names are not supported
     */
    public function validateNames(TableSchemaInterface $schema): void
    {
        foreach ($schema->columns() as $column) {
            if (!is_string($column) || !ctype_alpha($column[0])) {
                throw new StopException(sprintf(
                    'Unable to bake table with integer column names or names that start with digits. Found `%s`.',
                    (string)$column
                ));
            }
        }
    }

    /**
     * Get table context for baking a given table.
     *
     * @param \Cake\ORM\Table $tableObject The model name to generate.
     * @param string $table The table name for the model being baked.
     * @param string $name The model name to generate.
     * @param \Cake\Console\Arguments $args CLI Arguments
     * @param \Cake\Console\ConsoleIo $io CLI io
     * @return array
     */
    public function getTableContext(
        Table $tableObject,
        string $table,
        string $name,
        Arguments $args,
        ConsoleIo $io
    ): array {
        $associations = $this->getAssociations($tableObject, $args, $io);
        $this->applyAssociations($tableObject, $associations);
        $associationInfo = $this->getAssociationInfo($tableObject);

        $primaryKey = $this->getPrimaryKey($tableObject, $args);
        $displayField = $this->getDisplayField($tableObject, $args);
        $propertySchema = $this->getEntityPropertySchema($tableObject);
        $fields = $this->getFields($tableObject, $args);
        $validation = $this->getValidation($tableObject, $associations, $args);
        $rulesChecker = $this->getRules($tableObject, $associations, $args);
        $behaviors = $this->getBehaviors($tableObject);
        $connection = $this->connection;
        $hidden = $this->getHiddenFields($tableObject, $args);

        return compact(
            'associations',
            'associationInfo',
            'primaryKey',
            'displayField',
            'table',
            'propertySchema',
            'fields',
            'validation',
            'rulesChecker',
            'behaviors',
            'connection',
            'hidden'
        );
    }

    /**
     * Get a model object for a class name.
     *
     * @param string $className Name of class you want model to be.
     * @param string $table Table name
     * @return \Cake\ORM\Table Table instance
     */
    public function getTableObject(string $className, string $table): Table
    {
        if (!empty($this->plugin)) {
            $className = $this->plugin . '.' . $className;
        }

        if ($this->getTableLocator()->exists($className)) {
            return $this->getTableLocator()->get($className);
        }

        return $this->getTableLocator()->get($className, [
            'name' => $className,
            'table' => $this->tablePrefix . $table,
            'connection' => ConnectionManager::get($this->connection),
        ]);
    }

    /**
     * Get the array of associations to generate.
     *
     * @param \Cake\ORM\Table $table The table to get associations for.
     * @param \Cake\Console\Arguments $args CLI Arguments
     * @param \Cake\Console\ConsoleIo $io CLI io
     * @return array
     */
    public function getAssociations(Table $table, Arguments $args, ConsoleIo $io): array
    {
        if ($args->getOption('no-associations')) {
            return [];
        }
        $io->out('One moment while associations are detected.');

        $this->listAll();

        $associations = [
            'belongsTo' => [],
            'hasMany' => [],
            'belongsToMany' => [],
        ];

        $primary = $table->getPrimaryKey();
        $associations = $this->findBelongsTo($table, $associations);

        if (is_array($primary) && count($primary) > 1) {
            $io->warning(
                'Bake cannot generate associations for composite primary keys at this time.'
            );

            return $associations;
        }

        $associations = $this->findHasMany($table, $associations);
        $associations = $this->findBelongsToMany($table, $associations);

        return $associations;
    }

    /**
     * Sync the in memory table object.
     *
     * Composer's class cache prevents us from loading the
     * newly generated class. Applying associations if we have a
     * generic table object means fields will be detected correctly.
     *
     * @param \Cake\ORM\Table $model The table to apply associations to.
     * @param array $associations The associations to append.
     * @return void
     */
    public function applyAssociations(Table $model, array $associations): void
    {
        if (get_class($model) !== Table::class) {
            return;
        }
        foreach ($associations as $type => $assocs) {
            foreach ($assocs as $assoc) {
                $alias = $assoc['alias'];
                unset($assoc['alias']);
                $model->{$type}($alias, $assoc);
            }
        }
    }

    /**
     * Collects meta information for associations.
     *
     * The information returned is in the format of map, where the key is the
     * association alias:
     *
     * ```
     * [
     *     'associationAlias' => [
     *         'targetFqn' => '...'
     *     ],
     *     // ...
     * ]
     * ```
     *
     * @param \Cake\ORM\Table $table The table from which to collect association information.
     * @return array A map of association information.
     */
    public function getAssociationInfo(Table $table): array
    {
        $info = [];

        $appNamespace = Configure::read('App.namespace');

        foreach ($table->associations() as $association) {
            /** @var \Cake\ORM\Association $association */

            $tableClass = get_class($association->getTarget());
            if ($tableClass === Table::class) {
                $namespace = $appNamespace;

                $className = $association->getClassName();
                [$plugin, $className] = pluginSplit($className);
                if ($plugin !== null) {
                    $namespace = $plugin;
                }

                $namespace = str_replace('/', '\\', trim($namespace, '\\'));
                $tableClass = $namespace . '\Model\Table\\' . $className . 'Table';
            }

            $info[$association->getName()] = [
                'targetFqn' => '\\' . $tableClass,
            ];
        }

        return $info;
    }

    /**
     * Find belongsTo relations and add them to the associations list.
     *
     * @param \Cake\ORM\Table $model Database\Table instance of table being generated.
     * @param array $associations Array of in progress associations
     * @return array Associations with belongsTo added in.
     */
    public function findBelongsTo(Table $model, array $associations): array
    {
        $schema = $model->getSchema();
        foreach ($schema->columns() as $fieldName) {
            if (!preg_match('/^.+_id$/', $fieldName) || ($schema->getPrimaryKey() === [$fieldName])) {
                continue;
            }

            if ($fieldName === 'parent_id') {
                $className = $this->plugin ? $this->plugin . '.' . $model->getAlias() : $model->getAlias();
                $assoc = [
                    'alias' => 'Parent' . $model->getAlias(),
                    'className' => $className,
                    'foreignKey' => $fieldName,
                ];
            } else {
                $tmpModelName = $this->_modelNameFromKey($fieldName);
                if (!in_array(Inflector::tableize($tmpModelName), $this->_tables, true)) {
                    $found = $this->findTableReferencedBy($schema, $fieldName);
                    if ($found) {
                        $tmpModelName = Inflector::camelize($found);
                    }
                }
                $assoc = [
                    'alias' => $tmpModelName,
                    'foreignKey' => $fieldName,
                ];
                if ($schema->getColumn($fieldName)['null'] === false) {
                    $assoc['joinType'] = 'INNER';
                }
            }

            if ($this->plugin && empty($assoc['className'])) {
                $assoc['className'] = $this->plugin . '.' . $assoc['alias'];
            }
            $associations['belongsTo'][] = $assoc;
        }

        return $associations;
    }

    /**
     * find the table, if any, actually referenced by the passed key field.
     * Search tables in db for keyField; if found search key constraints
     * for the table to which it refers.
     *
     * @param \Cake\Database\Schema\TableSchemaInterface $schema The table schema to find a constraint for.
     * @param string $keyField The field to check for a constraint.
     * @return string|null Either the referenced table or null if the field has no constraints.
     */
    public function findTableReferencedBy(TableSchemaInterface $schema, string $keyField): ?string
    {
        if (!$schema->getColumn($keyField)) {
            return null;
        }

        foreach ($schema->constraints() as $constraint) {
            $constraintInfo = $schema->getConstraint($constraint);
            if (!in_array($keyField, $constraintInfo['columns'])) {
                continue;
            }

            if (!isset($constraintInfo['references'])) {
                continue;
            }
            $length = $this->tablePrefix ? mb_strlen($this->tablePrefix) : 0;
            if ($length > 0 && mb_substr($constraintInfo['references'][0], 0, $length) === $this->tablePrefix) {
                return mb_substr($constraintInfo['references'][0], $length);
            }

            return $constraintInfo['references'][0];
        }

        return null;
    }

    /**
     * Find the hasMany relations and add them to associations list
     *
     * @param \Cake\ORM\Table $model Model instance being generated
     * @param array $associations Array of in progress associations
     * @return array Associations with hasMany added in.
     */
    public function findHasMany(Table $model, array $associations): array
    {
        $schema = $model->getSchema();
        $primaryKey = $schema->getPrimaryKey();
        $tableName = $schema->name();
        $foreignKey = $this->_modelKey($tableName);

        $tables = $this->listAll();
        foreach ($tables as $otherTableName) {
            $otherModel = $this->getTableObject($this->_camelize($otherTableName), $otherTableName);
            $otherSchema = $otherModel->getSchema();

            $pregTableName = preg_quote($tableName, '/');
            $pregPattern = "/^{$pregTableName}_|_{$pregTableName}$/";
            if (preg_match($pregPattern, $otherTableName) === 1) {
                $possibleHABTMTargetTable = preg_replace($pregPattern, '', $otherTableName);
                if (in_array($possibleHABTMTargetTable, $tables)) {
                    continue;
                }
            }

            foreach ($otherSchema->columns() as $fieldName) {
                $assoc = false;
                if (!in_array($fieldName, $primaryKey) && $fieldName === $foreignKey) {
                    $assoc = [
                        'alias' => $otherModel->getAlias(),
                        'foreignKey' => $fieldName,
                    ];
                } elseif ($otherTableName === $tableName && $fieldName === 'parent_id') {
                    $className = $this->plugin ? $this->plugin . '.' . $model->getAlias() : $model->getAlias();
                    $assoc = [
                        'alias' => 'Child' . $model->getAlias(),
                        'className' => $className,
                        'foreignKey' => $fieldName,
                    ];
                }
                if ($assoc && $this->plugin && empty($assoc['className'])) {
                    $assoc['className'] = $this->plugin . '.' . $assoc['alias'];
                }
                if ($assoc) {
                    $associations['hasMany'][] = $assoc;
                }
            }
        }

        return $associations;
    }

    /**
     * Find the BelongsToMany relations and add them to associations list
     *
     * @param \Cake\ORM\Table $model Model instance being generated
     * @param array $associations Array of in-progress associations
     * @return array Associations with belongsToMany added in.
     */
    public function findBelongsToMany(Table $model, array $associations): array
    {
        $schema = $model->getSchema();
        $tableName = $schema->name();
        $foreignKey = $this->_modelKey($tableName);

        $tables = $this->listAll();
        foreach ($tables as $otherTableName) {
            $assocTable = null;
            $offset = strpos($otherTableName, $tableName . '_');
            $otherOffset = strpos($otherTableName, '_' . $tableName);

            if ($offset !== false) {
                $assocTable = substr($otherTableName, strlen($tableName . '_'));
            } elseif ($otherOffset !== false) {
                $assocTable = substr($otherTableName, 0, $otherOffset);
            }
            if ($assocTable && in_array($assocTable, $tables)) {
                $habtmName = $this->_camelize($assocTable);
                $assoc = [
                    'alias' => $habtmName,
                    'foreignKey' => $foreignKey,
                    'targetForeignKey' => $this->_modelKey($habtmName),
                    'joinTable' => $otherTableName,
                ];
                if ($this->plugin) {
                    $assoc['className'] = $this->plugin . '.' . $assoc['alias'];
                }
                $associations['belongsToMany'][] = $assoc;
            }
        }

        return $associations;
    }

    /**
     * Get the display field from the model or parameters
     *
     * @param \Cake\ORM\Table $model The model to introspect.
     * @param \Cake\Console\Arguments $args CLI Arguments
     * @return array<string>|string|null
     */
    public function getDisplayField(Table $model, Arguments $args)
    {
        if ($args->getOption('display-field')) {
            return (string)$args->getOption('display-field');
        }

        return $model->getDisplayField();
    }

    /**
     * Get the primary key field from the model or parameters
     *
     * @param \Cake\ORM\Table $model The model to introspect.
     * @param \Cake\Console\Arguments $args CLI Arguments
     * @return string[] The columns in the primary key
     */
    public function getPrimaryKey(Table $model, Arguments $args): array
    {
        if ($args->getOption('primary-key')) {
            $fields = explode(',', $args->getOption('primary-key'));

            return array_values(array_filter(array_map('trim', $fields)));
        }

        return (array)$model->getPrimaryKey();
    }

    /**
     * Returns an entity property "schema".
     *
     * The schema is an associative array, using the property names
     * as keys, and information about the property as the value.
     *
     * The value part consists of at least two keys:
     *
     * - `kind`: The kind of property, either `column`, which indicates
     * that the property stems from a database column, or `association`,
     * which identifies a property that is generated for an associated
     * table.
     * - `type`: The type of the property value. For the `column` kind
     * this is the database type associated with the column, and for the
     * `association` type it's the FQN of the entity class for the
     * associated table.
     *
     * For `association` properties an additional key will be available
     *
     * - `association`: Holds an instance of the corresponding association
     * class.
     *
     * @param \Cake\ORM\Table $model The model to introspect.
     * @return array The property schema
     */
    public function getEntityPropertySchema(Table $model): array
    {
        $properties = [];

        $schema = $model->getSchema();
        foreach ($schema->columns() as $column) {
            $columnSchema = $schema->getColumn($column);

            $properties[$column] = [
                'kind' => 'column',
                'type' => $columnSchema['type'],
                'null' => $columnSchema['null'],
            ];
        }

        foreach ($model->associations() as $association) {
            $entityClass = '\\' . ltrim($association->getTarget()->getEntityClass(), '\\');

            if ($entityClass === '\Cake\ORM\Entity') {
                $namespace = Configure::read('App.namespace');

                [$plugin, ] = pluginSplit($association->getTarget()->getRegistryAlias());
                if ($plugin !== null) {
                    $namespace = $plugin;
                }
                $namespace = str_replace('/', '\\', trim($namespace, '\\'));

                $entityClass = $this->_entityName($association->getTarget()->getAlias());
                $entityClass = '\\' . $namespace . '\Model\Entity\\' . $entityClass;
            }

            $properties[$association->getProperty()] = [
                'kind' => 'association',
                'association' => $association,
                'type' => $entityClass,
            ];
        }

        return $properties;
    }

    /**
     * Evaluates the fields and no-fields options, and
     * returns if, and which fields should be made accessible.
     *
     * If no fields are specified and the `no-fields` parameter is
     * not set, then all non-primary key fields + association
     * fields will be set as accessible.
     *
     * @param \Cake\ORM\Table $table The table instance to get fields for.
     * @param \Cake\Console\Arguments $args CLI Arguments
     * @return string[]|false|null Either an array of fields, `false` in
     *   case the no-fields option is used, or `null` if none of the
     *   field options is used.
     */
    public function getFields(Table $table, Arguments $args)
    {
        if ($args->getOption('no-fields')) {
            return false;
        }
        if ($args->getOption('fields')) {
            $fields = explode(',', $args->getOption('fields'));

            return array_values(array_filter(array_map('trim', $fields)));
        }
        $schema = $table->getSchema();
        $fields = $schema->columns();
        foreach ($table->associations() as $assoc) {
            $fields[] = $assoc->getProperty();
        }
        $primaryKey = $schema->getPrimaryKey();

        return array_values(array_diff($fields, $primaryKey));
    }

    /**
     * Get the hidden fields from a model.
     *
     * Uses the hidden and no-hidden options.
     *
     * @param \Cake\ORM\Table $model The model to introspect.
     * @param \Cake\Console\Arguments $args CLI Arguments
     * @return string[] The columns to make accessible
     */
    public function getHiddenFields(Table $model, Arguments $args): array
    {
        if ($args->getOption('no-hidden')) {
            return [];
        }
        if ($args->getOption('hidden')) {
            $fields = explode(',', $args->getOption('hidden'));

            return array_values(array_filter(array_map('trim', $fields)));
        }
        $schema = $model->getSchema();
        $columns = $schema->columns();
        $whitelist = ['token', 'password', 'passwd'];

        return array_values(array_intersect($columns, $whitelist));
    }

    /**
     * Generate default validation rules.
     *
     * @param \Cake\ORM\Table $model The model to introspect.
     * @param array $associations The associations list.
     * @param \Cake\Console\Arguments $args CLI Arguments
     * @return array|false The validation rules.
     */
    public function getValidation(Table $model, array $associations, Arguments $args)
    {
        if ($args->getOption('no-validation')) {
            return [];
        }
        $schema = $model->getSchema();
        $fields = $schema->columns();
        if (!$fields) {
            return false;
        }

        $validate = [];
        $primaryKey = $schema->getPrimaryKey();
        $foreignKeys = [];
        if (isset($associations['belongsTo'])) {
            foreach ($associations['belongsTo'] as $assoc) {
                $foreignKeys[] = $assoc['foreignKey'];
            }
        }
        foreach ($fields as $fieldName) {
            if (in_array($fieldName, $foreignKeys, true)) {
                continue;
            }
            $field = $schema->getColumn($fieldName);
            $validation = $this->fieldValidation($schema, $fieldName, $field, $primaryKey);
            if ($validation) {
                $validate[$fieldName] = $validation;
            }
        }

        return $validate;
    }

    /**
     * Does individual field validation handling.
     *
     * @param \Cake\Database\Schema\TableSchemaInterface $schema The table schema for the current field.
     * @param string $fieldName Name of field to be validated.
     * @param array $metaData metadata for field
     * @param array $primaryKey The primary key field
     * @return array Array of validation for the field.
     */
    public function fieldValidation(
        TableSchemaInterface $schema,
        string $fieldName,
        array $metaData,
        array $primaryKey
    ): array {
        $ignoreFields = ['lft', 'rght', 'created', 'modified', 'updated'];
        if (in_array($fieldName, $ignoreFields, true)) {
            return [];
        }

        $rules = [];
        if ($fieldName === 'email') {
            $rules['email'] = [];
        } elseif ($metaData['type'] === 'uuid') {
            $rules['uuid'] = [];
        } elseif ($metaData['type'] === 'integer') {
            if ($metaData['unsigned']) {
                $rules['nonNegativeInteger'] = [];
            } else {
                $rules['integer'] = [];
            }
        } elseif ($metaData['type'] === 'float') {
            $rules['numeric'] = [];
            if ($metaData['unsigned']) {
                $rules['greaterThanOrEqual'] = [
                    0,
                ];
            }
        } elseif ($metaData['type'] === 'decimal') {
            $rules['decimal'] = [];
            if ($metaData['unsigned']) {
                $rules['greaterThanOrEqual'] = [
                    0,
                ];
            }
        } elseif ($metaData['type'] === 'boolean') {
            $rules['boolean'] = [];
        } elseif ($metaData['type'] === 'date') {
            $rules['date'] = [];
        } elseif ($metaData['type'] === 'time') {
            $rules['time'] = [];
        } elseif (strpos($metaData['type'], 'datetime') === 0) {
            $rules['dateTime'] = [];
        } elseif (strpos($metaData['type'], 'timestamp') === 0) {
            $rules['dateTime'] = [];
        } elseif ($metaData['type'] === 'inet') {
            $rules['ip'] = [];
        } elseif (in_array($metaData['type'], ['char', 'string', 'text'], true)) {
            $rules['scalar'] = [];
            if ($metaData['length'] > 0) {
                $rules['maxLength'] = [$metaData['length']];
            }
        }

        $validation = [];
        foreach ($rules as $rule => $args) {
            $validation[$rule] = [
                'rule' => $rule,
                'args' => $args,
            ];
        }

        if (in_array($fieldName, $primaryKey, true)) {
            $validation['allowEmpty'] = [
                'rule' => $this->getEmptyMethod($fieldName, $metaData),
                'args' => [null, 'create'],
            ];
        } elseif ($metaData['null'] === true) {
            $validation['allowEmpty'] = [
                'rule' => $this->getEmptyMethod($fieldName, $metaData),
                'args' => [],
            ];
        } else {
            if ($metaData['default'] === null || $metaData['default'] === false) {
                $validation['requirePresence'] = [
                    'rule' => 'requirePresence',
                    'args' => ['create'],
                ];
            }
            $validation['notEmpty'] = [
                'rule' => $this->getEmptyMethod($fieldName, $metaData, 'not'),
                'args' => [],
            ];
        }

        foreach ($schema->constraints() as $constraint) {
            $constraint = $schema->getConstraint($constraint);
            if (!in_array($fieldName, $constraint['columns'], true) || count($constraint['columns']) > 1) {
                continue;
            }

            $timeTypes = [
                'datetime',
                'timestamp',
                'datetimefractional',
                'timestampfractional',
                'timestamptimezone',
                'date',
                'time',
            ];
            $notDatetime = !in_array($metaData['type'], $timeTypes, true);
            if ($constraint['type'] === TableSchema::CONSTRAINT_UNIQUE && $notDatetime) {
                $validation['unique'] = ['rule' => 'validateUnique', 'provider' => 'table'];
            }
        }

        return $validation;
    }

    /**
     * Get the specific allow empty method for field based on metadata.
     *
     * @param string $fieldName Field name.
     * @param array $metaData Field meta data.
     * @param string $prefix Method name prefix.
     * @return string
     */
    protected function getEmptyMethod(string $fieldName, array $metaData, string $prefix = 'allow'): string
    {
        switch ($metaData['type']) {
            case 'date':
                return $prefix . 'EmptyDate';

            case 'time':
                return $prefix . 'EmptyTime';

            case 'datetime':
            case 'datetimefractional':
            case 'timestamp':
            case 'timestampfractional':
            case 'timestamptimezone':
                return $prefix . 'EmptyDateTime';
        }

        if (preg_match('/file|image/', $fieldName)) {
            return $prefix . 'EmptyFile';
        }

        return $prefix . 'EmptyString';
    }

    /**
     * Generate default rules checker.
     *
     * @param \Cake\ORM\Table $model The model to introspect.
     * @param array $associations The associations for the model.
     * @param \Cake\Console\Arguments $args CLI Arguments
     * @return array The rules to be applied.
     */
    public function getRules(Table $model, array $associations, Arguments $args): array
    {
        if ($args->getOption('no-rules')) {
            return [];
        }
        $schema = $model->getSchema();
        $fields = $schema->columns();
        if (empty($fields)) {
            return [];
        }

        $uniqueColumns = ['username', 'login'];
        if (in_array($model->getAlias(), ['Users', 'Accounts'])) {
            $uniqueColumns[] = 'email';
        }

        $rules = [];
        foreach ($fields as $fieldName) {
            if (in_array($fieldName, $uniqueColumns, true)) {
                $rules[$fieldName] = ['name' => 'isUnique'];
            }
        }
        foreach ($schema->constraints() as $name) {
            $constraint = $schema->getConstraint($name);
            if ($constraint['type'] !== TableSchema::CONSTRAINT_UNIQUE) {
                continue;
            }
            if (count($constraint['columns']) > 1) {
                continue;
            }
            $rules[$constraint['columns'][0]] = ['name' => 'isUnique'];
        }

        if (empty($associations['belongsTo'])) {
            return $rules;
        }

        foreach ($associations['belongsTo'] as $assoc) {
            $rules[$assoc['foreignKey']] = ['name' => 'existsIn', 'extra' => $assoc['alias']];
        }

        return $rules;
    }

    /**
     * Get behaviors
     *
     * @param \Cake\ORM\Table $model The model to generate behaviors for.
     * @return array Behaviors
     */
    public function getBehaviors(Table $model): array
    {
        $behaviors = [];
        $schema = $model->getSchema();
        $fields = $schema->columns();
        if (empty($fields)) {
            return [];
        }
        if (in_array('created', $fields, true) || in_array('modified', $fields, true)) {
            $behaviors['Timestamp'] = [];
        }

        if (
            in_array('lft', $fields, true)
            && $schema->getColumnType('lft') === 'integer'
            && in_array('rght', $fields, true)
            && $schema->getColumnType('rght') === 'integer'
            && in_array('parent_id', $fields, true)
        ) {
            $behaviors['Tree'] = [];
        }

        $counterCache = $this->getCounterCache($model);
        if (!empty($counterCache)) {
            $behaviors['CounterCache'] = $counterCache;
        }

        return $behaviors;
    }

    /**
     * Get CounterCaches
     *
     * @param \Cake\ORM\Table $model The table to get counter cache fields for.
     * @return array<string, array> CounterCache configurations
     */
    public function getCounterCache(Table $model): array
    {
        $belongsTo = $this->findBelongsTo($model, ['belongsTo' => []]);
        $counterCache = [];
        foreach ($belongsTo['belongsTo'] as $otherTable) {
            $otherAlias = $otherTable['alias'];
            $otherModel = $this->getTableObject($this->_camelize($otherAlias), Inflector::underscore($otherAlias));

            try {
                $otherSchema = $otherModel->getSchema();
            } catch (Exception $e) {
                continue;
            }

            $otherFields = $otherSchema->columns();
            $alias = $model->getAlias();
            $field = Inflector::singularize(Inflector::underscore($alias)) . '_count';
            if (in_array($field, $otherFields, true)) {
                $counterCache[$otherAlias] = [$field];
            }
        }

        return $counterCache;
    }

    /**
     * Bake an entity class.
     *
     * @param \Cake\ORM\Table $model Model name or object
     * @param array $data An array to use to generate the Table
     * @param \Cake\Console\Arguments $args CLI Arguments
     * @param \Cake\Console\ConsoleIo $io CLI io
     * @return void
     */
    public function bakeEntity(Table $model, array $data, Arguments $args, ConsoleIo $io): void
    {
        if ($args->getOption('no-entity')) {
            return;
        }
        $name = $this->_entityName($model->getAlias());

        $namespace = Configure::read('App.namespace');
        $pluginPath = '';
        if ($this->plugin) {
            $namespace = $this->_pluginNamespace($this->plugin);
            $pluginPath = $this->plugin . '.';
        }

        $data += [
            'name' => $name,
            'namespace' => $namespace,
            'plugin' => $this->plugin,
            'pluginPath' => $pluginPath,
            'primaryKey' => [],
        ];

        $renderer = new TemplateRenderer($this->theme);
        $renderer->set($data);
        $out = $renderer->generate('Bake.Model/entity');

        $path = $this->getPath($args);
        $filename = $path . 'Entity' . DS . $name . '.php';
        $io->out("\n" . sprintf('Baking entity class for %s...', $name), 1, ConsoleIo::QUIET);
        $io->createFile($filename, $out, $args->getOption('force'));

        $emptyFile = $path . 'Entity' . DS . '.gitkeep';
        $this->deleteEmptyFile($emptyFile, $io);
    }

    /**
     * Bake a table class.
     *
     * @param \Cake\ORM\Table $model Model name or object
     * @param array $data An array to use to generate the Table
     * @param \Cake\Console\Arguments $args CLI Arguments
     * @param \Cake\Console\ConsoleIo $io CLI Arguments
     * @return void
     */
    public function bakeTable(Table $model, array $data, Arguments $args, ConsoleIo $io): void
    {
        if ($args->getOption('no-table')) {
            return;
        }

        $namespace = Configure::read('App.namespace');
        $pluginPath = '';
        if ($this->plugin) {
            $namespace = $this->_pluginNamespace($this->plugin);
        }

        $name = $model->getAlias();
        $entity = $this->_entityName($model->getAlias());
        $data += [
            'plugin' => $this->plugin,
            'pluginPath' => $pluginPath,
            'namespace' => $namespace,
            'name' => $name,
            'entity' => $entity,
            'associations' => [],
            'primaryKey' => 'id',
            'displayField' => null,
            'table' => null,
            'validation' => [],
            'rulesChecker' => [],
            'behaviors' => [],
            'connection' => $this->connection,
        ];

        $renderer = new TemplateRenderer($this->theme);
        $renderer->set($data);
        $out = $renderer->generate('Bake.Model/table');

        $path = $this->getPath($args);
        $filename = $path . 'Table' . DS . $name . 'Table.php';
        $io->out("\n" . sprintf('Baking table class for %s...', $name), 1, ConsoleIo::QUIET);
        $io->createFile($filename, $out, $args->getOption('force'));

        // Work around composer caching that classes/files do not exist.
        // Check for the file as it might not exist in tests.
        if (file_exists($filename)) {
            require_once $filename;
        }
        $this->getTableLocator()->clear();

        $emptyFile = $path . 'Table' . DS . '.gitkeep';
        $this->deleteEmptyFile($emptyFile, $io);
    }

    /**
     * Outputs the a list of possible models or controllers from database
     *
     * @return string[]
     */
    public function listAll(): array
    {
        if (!empty($this->_tables)) {
            return $this->_tables;
        }

        /** @var \Cake\Database\Connection $connection */
        $connection = ConnectionManager::get($this->connection);
        $scanner = new TableScanner($connection);
        $this->_tables = $scanner->listAll();

        return $this->_tables;
    }

    /**
     * Outputs the a list of unskipped models or controllers from database
     *
     * @return string[]
     */
    public function listUnskipped(): array
    {
        /** @var \Cake\Database\Connection $connection */
        $connection = ConnectionManager::get($this->connection);
        $scanner = new TableScanner($connection);

        return $scanner->listUnskipped();
    }

    /**
     * Get the table name for the model being baked.
     *
     * Uses the `table` option if it is set.
     *
     * @param string $name Table name
     * @param \Cake\Console\Arguments $args The CLI arguments
     * @return string
     */
    public function getTable(string $name, Arguments $args): string
    {
        if ($args->getOption('table')) {
            return (string)$args->getOption('table');
        }

        return Inflector::underscore($name);
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to configure
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = $this->_setCommonOptions($parser);

        $parser->setDescription(
            'Bake table and entity classes.'
        )->addArgument('name', [
            'help' => 'Name of the model to bake (without the Table suffix). ' .
                'You can use Plugin.name to bake plugin models.',
        ])->addOption('table', [
            'help' => 'The table name to use if you have non-conventional table names.',
        ])->addOption('no-entity', [
            'boolean' => true,
            'help' => 'Disable generating an entity class.',
        ])->addOption('no-table', [
            'boolean' => true,
            'help' => 'Disable generating a table class.',
        ])->addOption('no-validation', [
            'boolean' => true,
            'help' => 'Disable generating validation rules.',
        ])->addOption('no-rules', [
            'boolean' => true,
            'help' => 'Disable generating a rules checker.',
        ])->addOption('no-associations', [
            'boolean' => true,
            'help' => 'Disable generating associations.',
        ])->addOption('no-fields', [
            'boolean' => true,
            'help' => 'Disable generating accessible fields in the entity.',
        ])->addOption('fields', [
            'help' => 'A comma separated list of fields to make accessible.',
        ])->addOption('no-hidden', [
            'boolean' => true,
            'help' => 'Disable generating hidden fields in the entity.',
        ])->addOption('hidden', [
            'help' => 'A comma separated list of fields to hide.',
        ])->addOption('primary-key', [
            'help' => 'The primary key if you would like to manually set one.' .
                ' Can be a comma separated list if you are using a composite primary key.',
        ])->addOption('display-field', [
            'help' => 'The displayField if you would like to choose one.',
        ])->addOption('no-test', [
            'boolean' => true,
            'help' => 'Do not generate a test case skeleton.',
        ])->addOption('no-fixture', [
            'boolean' => true,
            'help' => 'Do not generate a test fixture skeleton.',
        ])->setEpilog(
            'Omitting all arguments and options will list the table names you can generate models for.'
        );

        return $parser;
    }

    /**
     * Interact with FixtureTask to automatically bake fixtures when baking models.
     *
     * @param string $className Name of class to bake fixture for
     * @param string $useTable Optional table name for fixture to use.
     * @param \Cake\Console\Arguments $args Arguments instance
     * @param \Cake\Console\ConsoleIo $io ConsoleIo instance
     * @return void
     */
    public function bakeFixture(
        string $className,
        string $useTable,
        Arguments $args,
        ConsoleIo $io
    ): void {
        if ($args->getOption('no-fixture')) {
            return;
        }
        $fixture = new FixtureCommand();
        $fixtureArgs = new Arguments(
            [$className],
            ['table' => $useTable] + $args->getOptions(),
            ['name']
        );
        $fixture->execute($fixtureArgs, $io);
    }

    /**
     * Assembles and writes a unit test file
     *
     * @param string $className Model class name
     * @param \Cake\Console\Arguments $args Arguments instance
     * @param \Cake\Console\ConsoleIo $io ConsoleIo instance
     * @return void
     */
    public function bakeTest(string $className, Arguments $args, ConsoleIo $io): void
    {
        if ($args->getOption('no-test')) {
            return;
        }
        $test = new TestCommand();
        $testArgs = new Arguments(
            ['table', $className],
            $args->getOptions(),
            ['type', 'name']
        );
        $test->execute($testArgs, $io);
    }
}
