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

/**
 * @internal
 */
class ParsedClass
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var array<string>
     */
    public $implements;

    /**
     * @var array<string, string>
     */
    public $constants;

    /**
     * @var array<string, string>
     */
    public $properties;

    /**
     * @var array<string, string>
     */
    public $methods;

    /**
     * @param string $name Class name
     * @param array<string> $implements List of implements
     * @param array<string, string> $constants Class constants
     * @param array<string, string> $properties Class properties
     * @param array<string, string> $methods Class methods
     */
    public function __construct(string $name, array $implements, array $constants, array $properties, array $methods)
    {
        $this->name = $name;
        $this->implements = $implements;
        $this->constants = $constants;
        $this->properties = $properties;
        $this->methods = $methods;
    }
}
