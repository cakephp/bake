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
class ParsedClass
{
    /**
     * @param string $name Class name
     * @param array<string> $implements List of implements
     * @param array<string, string> $constants Class constants
     * @param array<string, string> $properties Class properties
     * @param array<string, string> $methods Class methods
     */
    public function __construct(
        public string $name,
        public array $implements,
        public array $constants,
        public array $properties,
        public array $methods,
    ) {
    }
}
