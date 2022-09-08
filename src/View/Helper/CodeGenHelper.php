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
namespace Bake\View\Helper;

use Cake\View\Helper;

/**
 * Bake code generation helper
 */
class CodeGenHelper extends Helper
{
    /**
     * Builds php code from class, function and const imports.
     *
     * @param array<string|int, string> $classImports Class imports
     * @param array<string|int, string> $functionImports Function imports
     * @param array<string|int, string> $constImports Constant imports
     * @param string $prefix Code to prepend if final output is not empty
     * @param string $suffix Code to append if final output is not empty
     * @return string
     */
    public function getImports(
        array $classImports,
        array $functionImports,
        array $constImports,
        string $prefix = '',
        string $suffix = ''
    ): string {
        $classUses = $this->getUseStatements('use', $classImports);
        $functionUses = $this->getUseStatements('use function', $functionImports);
        $constUses = $this->getUseStatements('use const', $constImports);

        return $this->concat("\n", [$classUses, $functionUses, $constUses], $prefix, $suffix);
    }

    /**
     * Builds array of php use statements from imports.
     *
     * @param string $prefix Prefix to put before the name ('use', 'use function')
     * @param array<string|int, string> $imports Imports
     * @return array<string>
     */
    protected function getUseStatements(string $prefix, array $imports): array
    {
        $statements = [];
        foreach ($imports as $alias => $type) {
            if (is_int($alias)) {
                $statements[] = "{$prefix} {$type};";
                continue;
            }

            if ($type === $alias || substr($type, -strlen("\\{$alias}")) === "\\{$alias}") {
                $statements[] = "{$prefix} {$type};";
            } else {
                $statements[] = "{$prefix} {$type} as {$alias};";
            }
        }

        return $statements;
    }

    /**
     * Concats strings together with newlines between non-empty statements.
     *
     * @param string $delimiter Delimiter to separate strings
     * @param array<array<string>|string> $strings Strings to concatenate
     * @param string $prefix Code to prepend if final output is not empty
     * @param string $suffix Code to append if final output is not empty
     * @return string
     */
    public function concat(
        string $delimiter,
        array $strings,
        string $prefix = '',
        string $suffix = ''
    ): string {
        $output = implode(
            $delimiter,
            array_map(function ($string) use ($delimiter) {
                if (is_string($string)) {
                    return $string;
                }

                return implode($delimiter, array_filter($string));
            }, array_filter($strings))
        );

        if ($prefix && !empty($output)) {
            $output = $prefix . $output;
        }
        if ($suffix && !empty($output)) {
            $output .= $suffix;
        }

        return $output;
    }
}
