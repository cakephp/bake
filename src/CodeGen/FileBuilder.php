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

final class FileBuilder
{
    protected ClassBuilder $classBuilder;

    /**
     * @param string $namespace File namespace
     * @param \Bake\CodeGen\ParsedFile $parsedFile Parsed file it already exists
     */
    public function __construct(protected string $namespace, protected ?ParsedFile $parsedFile = null)
    {
        if ($parsedFile && $parsedFile->namespace !== $namespace) {
            throw new ParseException(sprintf(
                'Existing namespace `%s` does not match expected namespace `%s`, cannot update existing file',
                $parsedFile->namespace,
                $namespace
            ));
        }

        $this->classBuilder = new ClassBuilder($parsedFile?->class);
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
     * @return array<string|int, string>
     */
    public function getClassImports(array $include, array $ignored = []): array
    {
        return $this->getImports($include, $this->parsedFile?->classImports);
    }

    /**
     * Returns sorted list of class imports to include in generated file.
     *
     * @param array<string|int, string> $include Imports to always include
     * @return array<string|int, string>
     */
    public function getConstImports(array $include): array
    {
        return $this->getImports($include, $this->parsedFile?->constImports);
    }

    /**
     * Returns sorted list of class imports to include in generated file.
     *
     * @param array<string|int, string> $include Imports to always include
     * @return array<string|int, string>
     */
    public function getFunctionImports(array $include): array
    {
        return $this->getImports($include, $this->parsedFile?->functionImports);
    }

    /**
     * Returns sorted list of class imports to include in generated file.
     *
     * @param array<string|int, string> $include Imports to always include
     * @param array<string|int, string>|null $existing Imports from existing file
     * @return array<string|int, string>
     */
    protected function getImports(array $include, ?array $existing): array
    {
        $imports = $this->noramlizeImports($include);

        if ($existing) {
            $imports = $this->mergeImports($imports, $existing);
        }

        asort($imports, SORT_STRING);

        return $imports;
    }

    /**
     * @param array<string|int, string> $required Required imports for generated code
     * @return array<string, string>
     */
    protected function noramlizeImports(array $required): array
    {
        $uses = [];
        foreach ($required as $alias => $class) {
            if (is_int($alias)) {
                $last = strrpos($class, '\\', -1);
                if ($last !== false) {
                    $alias = substr($class, strrpos($class, '\\', -1) + 1);
                } else {
                    $alias = $class;
                }
            }

            if (isset($uses[$alias])) {
                throw new InvalidArgumentException(sprintf(
                    'Duplicate required import: alias `%s` already defined.',
                    $alias
                ));
            }

            if (in_array($class, $uses, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Duplicate required import: class `%s` already defined as `%s`.',
                    $class,
                    $alias
                ));
            }

            $uses[$alias] = $class;
        }

        return $uses;
    }

    /**
     * @param array<string, string> $existing Existing imports
     * @param array<string, string> $imports Imports to merge into existing
     * @return array<string, string>
     */
    protected function mergeImports(array $existing, array $imports): array
    {
        $uses = $existing;
        foreach ($imports as $alias => $class) {
            if (isset($uses[$alias])) {
                if ($uses[$alias] !== $class) {
                    Log::warning(sprintf(
                        'Existing import `%s` is already imported as `%s`, discarding',
                        $class,
                        $alias
                    ));
                }
                continue;
            }

            $foundAlias = array_search($class, $uses, true);
            if ($foundAlias !== false) {
                Log::warning(sprintf(
                    'Existing import `%s` is already imported as `%s`, discarding',
                    $class,
                    $foundAlias
                ));
                continue;
            }

            $uses[$alias] = $class;
        }

        return $uses;
    }
}
