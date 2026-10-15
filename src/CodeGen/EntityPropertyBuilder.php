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
 * @since         4.0.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\CodeGen;

use Cake\Database\Type\EnumType;
use Cake\Database\TypeFactory;
use Cake\ORM\Association;
use Cake\ORM\Entity;
use ReflectionProperty;

/**
 * Builds concrete PHP property declarations for entity classes.
 *
 * @internal
 */
class EntityPropertyBuilder
{
    /**
     * Builds a map of concrete PHP property declarations for an entity class.
     *
     * Declarations use `public protected(set)` visibility on declaring concrete properties.
     * Properties are not initialized, so fields which have not been hydrated, like
     * an association which has not been loaded, are uninitialized.
     *
     * Class types are resolved to their imported name when the class is part
     * of `$classImports`, to their short name when the class is part of
     * `$namespace`, and to their fully qualified class name otherwise.
     *
     * Property names used by `Cake\ORM\Entity` itself are skipped as those
     * fields have to remain dynamic fields.
     *
     * @see https://book.cakephp.org/6.x/orm/entities.html#declaring-concrete-properties
     * @param array<string, array<string, mixed>> $propertySchema The property schema to use for generating the declarations.
     * @param array<string, string> $classImports Class imports as [alias => class name] used for resolving type names.
     * @param string $namespace The namespace of the file the properties are generated for.
     * @return array<string, string> Map of property name to property declaration.
     */
    public function buildEntityPropertyDeclarations(
        array $propertySchema,
        array $classImports = [],
        string $namespace = '',
    ): array {
        $imports = ImportHelper::normalize($classImports);

        $declarations = [];
        foreach ($this->entityPropertyTypes($propertySchema) as $property => $info) {
            $type = $info['type'];
            if ($info['class'] !== null) {
                $type = $this->resolvePropertyType($info['class'], $type, $imports, $namespace);
            }

            $declarations[$property] = "public protected(set) {$type} \${$property};";
        }

        return $declarations;
    }

    /**
     * Builds the list of classes used by the concrete property declarations
     * of an entity class so they can be added to the file's imports.
     *
     * Classes that are part of `$namespace` are not included as they can be
     * referenced by their short name without an import.
     *
     * @see https://book.cakephp.org/6.x/orm/entities.html#declaring-concrete-properties
     * @param array<string, array<string, mixed>> $propertySchema The property schema to use for generating the imports.
     * @param string $namespace The namespace of the file the properties are generated for.
     * @return array<int, string> List of fully qualified class names without a leading backslash.
     */
    public function buildEntityPropertyImports(array $propertySchema, string $namespace = ''): array
    {
        $imports = [];
        foreach ($this->entityPropertyTypes($propertySchema) as $info) {
            if ($info['class'] === null) {
                continue;
            }

            $class = ltrim($info['class'], '\\');
            if ($namespace !== '' && str_starts_with($class, $namespace . '\\')) {
                continue;
            }

            $imports[] = $class;
        }

        return array_values(array_unique($imports));
    }

    /**
     * Converts a column type to its DocBlock type counterpart.
     *
     * This method only supports the default CakePHP column types,
     * for custom column/database types `'string'` will be returned.
     *
     * @see \Cake\Database\Type
     * @param string $type The column type.
     * @return string|null The DocBlock type, or `'string'` for unsupported column types.
     */
    public function columnTypeToHintType(string $type): ?string
    {
        switch ($type) {
            case 'char':
            case 'string':
            case 'text':
            case 'uuid':
            case 'decimal':
                return 'string';

            case 'integer':
            case 'biginteger':
            case 'smallinteger':
            case 'tinyinteger':
                return 'int';

            case 'float':
                return 'float';

            case 'boolean':
                return 'bool';

            case 'array':
            case 'json':
                return 'array';

            case 'binary':
                return 'string|resource';

            case 'date':
                $dbType = TypeFactory::build($type);
                if (method_exists($dbType, 'getDateClassName')) {
                    // allow custom Date class which should extend \Cake\I18n\Date
                    return '\\' . $dbType->getDateClassName();
                }

                return '\Cake\I18n\Date';

            case 'datetime':
            case 'datetimefractional':
            case 'timestamp':
            case 'timestampfractional':
            case 'timestamptimezone':
                $dbType = TypeFactory::build($type);
                if (method_exists($dbType, 'getDateTimeClassName')) {
                    // allow custom DateTime class which should extend \Cake\I18n\DateTime
                    return '\\' . $dbType->getDateTimeClassName();
                }

                return '\Cake\I18n\DateTime';

            case 'time':
                $dbType = TypeFactory::build($type);
                if (method_exists($dbType, 'getTimeClassName')) {
                    // allow custom Time class which should extend \Cake\I18n\Time
                    return '\\' . $dbType->getTimeClassName();
                }

                return '\Cake\I18n\Time';

            default:
                if (str_starts_with($type, 'enum-')) {
                    $dbType = TypeFactory::build($type);
                    if ($dbType instanceof EnumType) {
                        return '\\' . $dbType->getEnumClassName();
                    }
                }
        }

        // Any unique or custom types will have a `string` type hint
        return 'string';
    }

    /**
     * Converts an entity class type to its DocBlock hint type counterpart.
     *
     * @param string $type The entity class type (a fully qualified class name).
     * @param \Cake\ORM\Association $association The association related to the entity class.
     * @return string The DocBlock type
     */
    public function associatedEntityTypeToHintType(string $type, Association $association): string
    {
        $annotationType = $association->type();
        if (
            $annotationType === Association::MANY_TO_MANY ||
            $annotationType === Association::ONE_TO_MANY
        ) {
            return 'array<' . $type . '>';
        }

        return $type;
    }

    /**
     * Resolves a class based property type to the shortest correct name.
     *
     * Imported classes are referenced by their alias, classes part of the
     * file's namespace by their short name when no import shadows it, and
     * all other classes by their fully qualified class name.
     *
     * @param string $class The fully qualified class name with a leading backslash.
     * @param string $type The property type containing the class name.
     * @param array<string, string> $imports Class imports as [alias => class name].
     * @param string $namespace The namespace of the file the type is used in.
     * @return string The resolved property type.
     */
    protected function resolvePropertyType(string $class, string $type, array $imports, string $namespace): string
    {
        $alias = array_search(ltrim($class, '\\'), $imports, true);
        if (is_string($alias)) {
            return str_replace($class, $alias, $type);
        }

        $shortName = substr($class, strrpos($class, '\\') + 1);
        $isSameNamespace = $namespace !== '' && str_starts_with(ltrim($class, '\\'), $namespace . '\\');
        if ($isSameNamespace && !isset($imports[$shortName])) {
            return str_replace($class, $shortName, $type);
        }

        return $type;
    }

    /**
     * Builds the PHP type information used for an entity's concrete properties.
     *
     * @param array<string, array<string, mixed>> $propertySchema The property schema to use for generating the type information.
     * @return array<string, array{type: string, class: string|null}> Map of property name to type information.
     */
    protected function entityPropertyTypes(array $propertySchema): array
    {
        $reserved = $this->reservedEntityPropertyNames();

        $types = [];
        foreach ($propertySchema as $property => $info) {
            if (isset($reserved[$property])) {
                continue;
            }

            if ($info['kind'] === 'column') {
                $type = $this->columnTypeToHintType($info['type']) ?? 'string';
                if (str_contains($type, '|')) {
                    // Union types like `string|resource` have no matching PHP type.
                    $types[$property] = ['type' => 'mixed', 'class' => null];

                    continue;
                }

                // Class types like `\Cake\I18n\DateTime` start with a backslash.
                $types[$property] = [
                    'type' => (!empty($info['null']) ? '?' : '') . $type,
                    'class' => str_starts_with($type, '\\') ? $type : null,
                ];

                continue;
            }

            $type = $this->associatedEntityTypeToHintType($info['type'], $info['association']);
            if (str_ends_with($type, '[]') || str_starts_with($type, 'array<')) {
                // PHP types cannot express an array of entities,
                // the `@property` annotation keeps that hint.
                $types[$property] = ['type' => '?array', 'class' => null];

                continue;
            }

            // Association types are nullable as loading an association
            // which has no result sets the field to `null`.
            $types[$property] = ['type' => '?' . $type, 'class' => $type];
        }

        return $types;
    }

    /**
     * Gets the property names that cannot be declared on entity classes as
     * they are used by `Cake\ORM\Entity` itself.
     *
     * @return array<string, true> The reserved property names.
     */
    protected function reservedEntityPropertyNames(): array
    {
        $properties = new ReflectionProperty(Entity::class, 'restrictedProperties')->getValue();

        return is_array($properties) ? $properties : [];
    }
}
