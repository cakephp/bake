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
 * @since         2.8.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\CodeGen;

use Cake\Log\Log;

class FileBuilder
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var \Bake\CodeGen\ParsedFile|null
     */
    protected $parsedFile;

    /**
     * @var \Bake\CodeGen\ClassBuilder
     */
    protected $classBuilder;

    /**
     * @param string $namespace File namespace
     * @param \Bake\CodeGen\ParsedFile $parsedFile Parsed file it already exists
     */
    public function __construct(string $namespace, ?ParsedFile $parsedFile = null)
    {
        if ($parsedFile && $parsedFile->namespace !== $namespace) {
            throw new ParseException(sprintf(
                'Existing namespace `%s` does not match expected namespace `%s`, cannot update existing file',
                $parsedFile->namespace,
                $namespace
            ));
        }

        $this->namespace = $namespace;
        $this->parsedFile = $parsedFile;
        $this->classBuilder = new ClassBuilder($parsedFile->class ?? null);
    }

    /**
     * Returns the file namespace.
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return \Bake\CodeGen\ClassBuilder
     */
    public function classBuilder(): ClassBuilder
    {
        return $this->classBuilder;
    }

    /**
     * Returns sorted list of class imports to include in generated file.
     *
     * @param array<string|int, string> $generatedClasses Generated class imports
     * @param array<string|int, string> $generatedFunctions Generated function imports
     * @param array<string|int, string> $generatedConsts Generated const imports
     * @return array<string, array<string>>
     */
    public function getUses(
        array $generatedClasses = [],
        array $generatedFunctions = [],
        array $generatedConsts = []
    ): array {
        $uses = [];

        $imports = $this->mergeUserImports($generatedClasses, $this->parsedFile->imports['class'] ?? []);
        foreach ($imports as $alias => $type) {
            $uses['class'][] = $this->getUse('class', $alias, $type);
        }

        $imports = $this->mergeUserImports($generatedFunctions, $this->parsedFile->imports['function'] ?? []);
        foreach ($imports as $alias => $type) {
            $uses['function'][] = $this->getUse('function', $alias, $type);
        }

        $imports = $this->mergeUserImports($generatedConsts, $this->parsedFile->imports['const'] ?? []);
        foreach ($imports as $alias => $type) {
            $uses['const'][] = $this->getUse('const', $alias, $type);
        }

        return $uses;
    }

    /**
     * Builds a use statement.
     *
     * @param string $section Use section "class', 'function' or 'const
     * @param string $alias Import alias
     * @param string $type Import type
     * @return string
     */
    protected function getUse(string $section, string $alias, string $type): string
    {
        $prefix = '';
        switch ($section) {
            case 'class':
                $prefix = 'use';
                break;
            case 'function':
                $prefix = 'use function';
                break;
            case 'const':
                $prefix = 'use const';
                break;
        }

        if ($type == $alias || substr($type, -strlen("\\{$alias}")) === "\\{$alias}") {
            return "{$prefix} {$type};";
        }

        return "{$prefix} {$type} as {$alias};";
    }

    /**
     * Normalizes imports included from generated code into [alias => name] format.
     *
     * @param array<string|int, string> $imports Imports
     * @return array<string, string>
     */
    protected function normalizeIncludes(array $imports): array
    {
        $normalized = [];
        foreach ($imports as $alias => $class) {
            if (is_int($alias)) {
                $last = strrpos($class, '\\', -1);
                if ($last !== false) {
                    $alias = substr($class, strrpos($class, '\\', -1) + 1);
                } else {
                    $alias = $class;
                }
            }

            $normalized[$alias] = $class;
        }

        return $normalized;
    }

    /**
     * @param array<string|int, string> $generated Generated imports to merge into
     * @param array<string, string> $user User imports to merge
     * @return array<string, string>
     */
    protected function mergeUserImports(array $generated, array $user): array
    {
        $generated = $this->normalizeIncludes($generated);

        $imports = $generated;
        foreach ($user as $alias => $class) {
            if (isset($generated[$alias]) && $generated[$alias] !== $class) {
                Log::warning(sprintf(
                    'User import `%s` conflicts with generated import, discarding',
                    $class
                ));
                continue;
            }

            $generatedAlias = array_search($class, $generated, true);
            if ($generatedAlias !== false && $generatedAlias != $alias) {
                Log::warning(sprintf(
                    'User import `%s` conflicts with generated import, discarding',
                    $class
                ));
                continue;
            }

            $imports[$alias] = $class;
        }

        asort($imports, SORT_STRING | SORT_FLAG_CASE);

        return $imports;
    }
}
