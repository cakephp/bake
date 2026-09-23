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
 * @since         2.8.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\CodeGen;

/**
 * @internal
 */
class ParsedFile
{
    /**
     * @param string $namespace Namespace
     * @param array<string, string> $classImports Class imports
     * @param array<string, string> $functionImports Function imports
     * @param array<string, string> $constImports Const imports
     * @param \Bake\CodeGen\ParsedClass $class Parsed class
     */
    public function __construct(
        public string $namespace,
        public array $classImports,
        public array $functionImports,
        public array $constImports,
        public ParsedClass $class,
    ) {
    }
}
