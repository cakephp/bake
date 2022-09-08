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

class ClassBuilder
{
    /**
     * @var \Bake\CodeGen\ParsedClass|null
     */
    protected $parsedClass;

    /**
     * @param \Bake\CodeGen\ParsedClass $parsedClass Parsed class it already exists
     */
    public function __construct(?ParsedClass $parsedClass = null)
    {
        $this->parsedClass = $parsedClass;
    }

    /**
     * Returns the user functions from existing file.
     *
     * @param array<string> $generated Constants that are generated
     * @return array<string, string>
     */
    public function getUserConstants(array $generated = []): array
    {
        if ($this->parsedClass === null) {
            return [];
        }

        return array_diff_key($this->parsedClass->constants, array_flip($generated));
    }

    /**
     * Returns the user functions from existing file.
     *
     * @param array<string> $generated Proeprties that are generated
     * @return array<string, string>
     */
    public function getUserProperties(array $generated = []): array
    {
        if ($this->parsedClass === null) {
            return [];
        }

        return array_diff_key($this->parsedClass->properties, array_flip($generated));
    }

    /**
     * Returns the user functions from existing file.
     *
     * @param array<string> $generated Methods that are generated
     * @return array<string, string>
     */
    public function getUserFunctions(array $generated = []): array
    {
        if ($this->parsedClass === null) {
            return [];
        }

        return array_diff_key($this->parsedClass->methods, array_flip($generated));
    }
}
