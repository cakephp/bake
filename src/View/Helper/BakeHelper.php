<?php
declare(strict_types=1);

namespace Bake\View\Helper;

use Bake\Utility\Model\AssociationFilter;
use Brick\VarExporter\VarExporter;
use Cake\Core\Configure;
use Cake\Core\ConventionsTrait;
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\SchemaInterface;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use Cake\View\Helper;

/**
 * Bake helper
 */
class BakeHelper extends Helper
{
    use ConventionsTrait;

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [];

    /**
     * AssociationFilter utility
     *
     * @var \Bake\Utility\Model\AssociationFilter|null
     */
    protected $_associationFilter = null;

    /**
     * Used for generating formatted properties such as component and helper arrays
     *
     * @param string $name the name of the property
     * @param array $value the array of values
     * @param array<string,mixed> $options extra options to be passed to the element
     * @return string
     */
    public function arrayProperty(string $name, array $value = [], array $options = []): string
    {
        if (!$value) {
            return '';
        }

        foreach ($value as &$val) {
            $val = Inflector::camelize($val);
        }
        $options += [
            'name' => $name,
            'value' => $value,
        ];

        return $this->_View->element('array_property', $options);
    }

    /**
     * Returns an array converted into a formatted multiline string
     *
     * @param array $list array of items to be stringified
     * @param array<string, mixed> $options options to use
     * @return string
     * @deprecated 2.5.0 Use BakeHelper::exportVar() instead.
     */
    public function stringifyList(array $list, array $options = []): string
    {
        $defaults = [
            'indent' => 2,
            'tab' => '    ',
            'trailingComma' => !isset($options['indent']) || $options['indent'] ? true : false,
            'quotes' => true,
        ];
        $options += $defaults;

        if (!$list) {
            return '';
        }

        foreach ($list as $k => &$v) {
            if ($options['quotes']) {
                $v = "'$v'";
            }
            if (!is_numeric($k)) {
                $nestedOptions = $options;
                if ($nestedOptions['indent']) {
                    $nestedOptions['indent'] += 1;
                }
                if (is_array($v)) {
                    $v = sprintf(
                        "'%s' => [%s]",
                        $k,
                        $this->stringifyList($v, $nestedOptions)
                    );
                } else {
                    $v = "'$k' => $v";
                }
            } elseif (is_array($v)) {
                $nestedOptions = $options;
                if ($nestedOptions['indent']) {
                    $nestedOptions['indent'] += 1;
                }
                $v = sprintf(
                    '[%s]',
                    $this->stringifyList($v, $nestedOptions)
                );
            }
        }

        $start = $end = '';
        $join = ', ';
        if ($options['indent']) {
            $join = ',';
            $start = "\n" . str_repeat($options['tab'], $options['indent']);
            $join .= $start;
            $end = "\n" . str_repeat($options['tab'], $options['indent'] - 1);
        }

        if ($options['trailingComma'] && $options['indent'] > 0) {
            $end = ',' . $end;
        }

        return $start . implode($join, $list) . $end;
    }

    /**
     * Export variable to string representation.
     *
     * (Similar to `var_export()` but better).
     *
     * @param mixed $var Variable to export.
     * @param int $indentLevel Identation level.
     * @param int $options VarExporter option flags
     * @return string
     * @throws \Brick\VarExporter\ExportException
     * @see https://github.com/brick/varexporter#options
     */
    public function exportVar($var, int $indentLevel = 0, int $options = 0): string
    {
        $options |= VarExporter::TRAILING_COMMA_IN_ARRAY;

        return VarExporter::export($var, $options, $indentLevel);
    }

    /**
     * Export array to string representation.
     *
     * (Similar to `var_export()` but better).
     *
     * @param array $var Array to export.
     * @param int $indentLevel Identation level.
     * @param bool $inline Inline numeric scalar array (adds INLINE_NUMERIC_SCALAR_ARRAY flag)
     * @return string
     */
    public function exportArray(array $var, int $indentLevel = 0, bool $inline = true): string
    {
        $options = 0;
        if ($inline) {
            $options = VarExporter::INLINE_NUMERIC_SCALAR_ARRAY;
        }

        return $this->exportVar($var, $indentLevel, $options);
    }

    /**
     * Extract the aliases for associations, filters hasMany associations already extracted as
     * belongsToMany
     *
     * @param \Cake\ORM\Table $table object to find associations on
     * @param string $assoc association to extract
     * @return string[]
     */
    public function aliasExtractor(Table $table, string $assoc): array
    {
        $extractor = function ($val) {
            return $val->getTarget()->getAlias();
        };
        $aliases = array_map($extractor, $table->associations()->getByType($assoc));
        if ($assoc === 'HasMany') {
            return $this->_filterHasManyAssociationsAliases($table, $aliases);
        }

        return $aliases;
    }

    /**
     * Returns details about the given class.
     *
     * The returned array holds the following keys:
     *
     * - `fqn` (the fully qualified name)
     * - `namespace` (the full namespace without leading separator)
     * - `class` (the class name)
     * - `plugin` (either the name of the plugin, or `null`)
     * - `name` (the name of the component without suffix)
     * - `fullName` (the full name of the class, including possible vendor and plugin name)
     *
     * @param string $class Class name
     * @param string $type Class type/sub-namespace
     * @param string $suffix Class name suffix
     * @return array Class info
     */
    public function classInfo(string $class, string $type, string $suffix): array
    {
        [$plugin, $name] = \pluginSplit($class);

        $base = Configure::read('App.namespace');
        if ($plugin !== null) {
            $base = $plugin;
        }
        $base = str_replace('/', '\\', trim($base, '\\'));
        $sub = '\\' . str_replace('/', '\\', trim($type, '\\'));
        $qn = $sub . '\\' . $name . $suffix;

        if (class_exists('\Cake' . $qn)) {
            $base = 'Cake';
        }

        return [
            'fqn' => '\\' . $base . $qn,
            'namespace' => $base . $sub,
            'plugin' => $plugin,
            'class' => $name . $suffix,
            'name' => $name,
            'fullName' => $class,
        ];
    }

    /**
     * Return list of fields to generate controls for.
     *
     * @param array $fields Fields list.
     * @param \Cake\Datasource\SchemaInterface $schema Schema instance.
     * @param \Cake\ORM\Table|null $modelObject Model object.
     * @param string|int $takeFields Take fields.
     * @param array<string> $filterTypes Filter field types.
     * @return array
     */
    public function filterFields(
        array $fields,
        SchemaInterface $schema,
        ?Table $modelObject = null,
        $takeFields = 0,
        array $filterTypes = ['binary']
    ): array {
        $fields = collection($fields)
            ->filter(function ($field) use ($schema, $filterTypes) {
                return !in_array($schema->getColumnType($field), $filterTypes);
            });

        if (isset($modelObject) && $modelObject->hasBehavior('Tree')) {
            $fields = $fields->reject(function ($field) {
                return $field === 'lft' || $field === 'rght';
            });
        }

        if (!empty($takeFields)) {
            $fields = $fields->take((int)$takeFields);
        }

        return $fields->toArray();
    }

    /**
     * Get fields data for view template.
     *
     * @param array $fields Fields list.
     * @param \Cake\Datasource\SchemaInterface $schema Schema instance.
     * @param array $associations Associations data.
     * @return array
     */
    public function getViewFieldsData(array $fields, SchemaInterface $schema, array $associations): array
    {
        $immediateAssociations = $associations['BelongsTo'];
        $associationFields = collection($fields)
            ->map(function ($field) use ($immediateAssociations) {
                foreach ($immediateAssociations as $alias => $details) {
                    if ($field === $details['foreignKey']) {
                        return [$field => $details];
                    }
                }
            })
            ->filter()
            ->reduce(function ($fields, $value) {
                return $fields + $value;
            }, []);

        $groupedFields = collection($fields)
            ->filter(function ($field) use ($schema) {
                return $schema->getColumnType($field) !== 'binary';
            })
            ->groupBy(function ($field) use ($schema, $associationFields) {
                $type = $schema->getColumnType($field);
                if (isset($associationFields[$field])) {
                    return 'string';
                }
                $numberTypes = ['decimal', 'biginteger', 'integer', 'float', 'smallinteger', 'tinyinteger'];
                if (in_array($type, $numberTypes, true)) {
                    return 'number';
                }
                $dateTypes = [
                    'date',
                    'time',
                    'datetime',
                    'datetimefractional',
                    'timestamp',
                    'timestampfractional',
                    'timestamptimezone',
                ];
                if (in_array($type, $dateTypes)) {
                    return 'date';
                }

                return in_array($type, ['text', 'boolean']) ? $type : 'string';
            })
            ->toArray();

        $groupedFields += [
            'number' => [],
            'string' => [],
            'boolean' => [],
            'date' => [],
            'text' => [],
        ];

        return compact('associationFields', 'groupedFields');
    }

    /**
     * Get column data from schema.
     *
     * @param string $field Field name.
     * @param \Cake\Database\Schema\TableSchema $schema Schema.
     * @return array|null
     */
    public function columnData(string $field, TableSchema $schema): ?array
    {
        return $schema->getColumn($field);
    }

    /**
     * Get alias of associated table.
     *
     * @param \Cake\ORM\Table $modelObj Model object.
     * @param string $assoc Association name.
     * @return string
     */
    public function getAssociatedTableAlias(Table $modelObj, string $assoc): string
    {
        $association = $modelObj->getAssociation($assoc);

        return $association->getTarget()->getAlias();
    }

    /**
     * Get validation methods data.
     *
     * @param string $field Field name.
     * @param array $rules Validation rules list.
     * @return string[]
     */
    public function getValidationMethods(string $field, array $rules): array
    {
        $validationMethods = [];

        foreach ($rules as $ruleName => $rule) {
            if ($rule['rule'] && !isset($rule['provider']) && !isset($rule['args'])) {
                $validationMethods[] = sprintf("->%s('%s')", $rule['rule'], $field);
                continue;
            }

            if ($rule['rule'] && isset($rule['provider'])) {
                $validationMethods[] = sprintf(
                    "->add('%s', '%s', ['rule' => '%s', 'provider' => '%s'])",
                    $field,
                    $ruleName,
                    $rule['rule'],
                    $rule['provider']
                );
                continue;
            }

            if (empty($rule['args'])) {
                $validationMethods[] = sprintf(
                    "->%s('%s')",
                    $rule['rule'],
                    $field
                );
                continue;
            }

            $rule['args'] = array_map(function ($item) {
                return $this->exportVar(
                    $item,
                    is_array($item) ? 3 : 0,
                    VarExporter::INLINE_NUMERIC_SCALAR_ARRAY
                );
            }, $rule['args']);
            $validationMethods[] = sprintf(
                "->%s('%s', %s)",
                $rule['rule'],
                $field,
                implode(', ', $rule['args'])
            );
        }

        return $validationMethods;
    }

    /**
     * Get field accessibility data.
     *
     * @param string[]|false|null $fields Fields list.
     * @param string[]|null $primaryKey Primary key.
     * @return array<string, bool>
     */
    public function getFieldAccessibility($fields = null, $primaryKey = null): array
    {
        $accessible = [];

        if (!isset($fields) || $fields !== false) {
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $accessible[$field] = true;
                }
            } elseif (!empty($primaryKey)) {
                $accessible['*'] = true;
                foreach ($primaryKey as $field) {
                    $accessible[$field] = false;
                }
            }
        }

        return $accessible;
    }

    /**
     * Wrap string arguments with quotes
     *
     * @param array $args array of arguments
     * @return array
     */
    public function escapeArguments(array $args): array
    {
        return array_map(function ($v) {
            if (is_string($v)) {
                $v = strtr($v, ["'" => "\'"]);
                $v = "'$v'";
            }

            return $v;
        }, $args);
    }

    /**
     * To be mocked elsewhere...
     *
     * @param \Cake\ORM\Table $table Table
     * @param string[] $aliases array of aliases
     * @return string[]
     */
    protected function _filterHasManyAssociationsAliases(Table $table, array $aliases): array
    {
        if (is_null($this->_associationFilter)) {
            $this->_associationFilter = new AssociationFilter();
        }

        return $this->_associationFilter->filterHasManyAssociationsAliases($table, $aliases);
    }
}
