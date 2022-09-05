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

/**
 * @internal
 */
final class ParsedFile
{
    /**
     * @param string $namespace Namespace
     * @param array<string|int, string> $classImports Class imports
     * @param array<string|int, string> $functionImports Function imports
     * @param array<string|int, string> $constImports Const imports
     * param \Bake\CodeGen\ParsedClass $class Class defined in file
     */
    public function __construct(
        public readonly string $namespace,
        public readonly array $classImports,
        public readonly array $functionImports,
        public readonly array $constImports,
        public readonly ParsedClass $class
    ) {
    }
}
