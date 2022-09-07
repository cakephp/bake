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
 * @since         3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\CodeGen;

use Cake\Log\Log;
use InvalidArgumentException;

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
     * @return \Bake\CodeGen\ClassBuilder
     */
    public function classBuilder(): ClassBuilder
    {
        return $this->classBuilder;
    }

    /**
     * Returns sorted list of class imports to include in generated file.
     *
     * @param array<string|int, string> $include Imports to always include
     * @return array<string, string>
     */
    public function getClassImports(array $include): array
    {
        return $this->getImports($include, $this->parsedFile->classImports ?? null);
    }

    /**
     * Returns sorted list of class imports to include in generated file.
     *
     * @param array<string|int, string> $include Imports to always include
     * @return array<string, string>
     */
    public function getConstImports(array $include): array
    {
        return $this->getImports($include, $this->parsedFile->constImports ?? null);
    }

    /**
     * Returns sorted list of class imports to include in generated file.
     *
     * @param array<string|int, string> $include Imports to always include
     * @return array<string, string>
     */
    public function getFunctionImports(array $include): array
    {
        return $this->getImports($include, $this->parsedFile->functionImports ?? null);
    }

    /**
     * Returns sorted list of class imports to include in generated file.
     *
     * @param array<string|int, string> $include Imports to always include
     * @param array<string, string>|null $parsed Imports from existing file
     * @return array<string, string>
     */
    protected function getImports(array $include, ?array $parsed): array
    {
        $imports = $this->normalizeIncludes($include);

        if ($parsed) {
            $imports = $this->mergeParsedImports($imports, $parsed);
        }

        asort($imports, SORT_STRING);

        return $imports;
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

            if (array_search($class, $normalized, true) !== false) {
                throw new InvalidArgumentException(sprintf(
                    'Cannot specify duplicate import for `%s`',
                    $class
                ));
            }

            $normalized[$alias] = $class;
        }

        return $normalized;
    }

    /**
     * @param array<string, string> $imports Generated imports to merge into
     * @param array<string, string> $parsed Imports User imports to merge
     * @return array<string, string>
     */
    protected function mergeParsedImports(array $imports, array $parsed): array
    {
        foreach ($parsed as $alias => $class) {
            if (isset($imports[$alias]) && $imports[$alias] !== $class) {
                Log::warning(sprintf(
                    'Import conflict: alias `%s` is already being used by generated code, discarding',
                    $alias
                ));
                continue;
            }

            if (array_search($class, $imports, true) !== false) {
                Log::warning(sprintf(
                    'Import conflict: `%s` in generated code is already imported with a different alias, discarding',
                    $class
                ));
                continue;
            }

            $imports[$alias] = $class;
        }

        return $imports;
    }
}
