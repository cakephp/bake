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
class ParsedFile
{
    /**
     * @var string
     */
    public $namespace;

    /**
     * @var array<string, string>
     */
    public $classImports;

    /**
     * @var array<string, string>
     */
    public $functionImports;

    /**
     * @var array<string, string>
     */
    public $constImports;

    /**
     * @var \Bake\CodeGen\ParsedClass
     */
    public $class;

    /**
     * @param string $namespace Namespace
     * @param array<string, string> $classImports Class imports
     * @param array<string, string> $functionImports Function imports
     * @param array<string, string> $constImports Const imports
     * @param \Bake\CodeGen\ParsedClass $class Parsed class
     */
    public function __construct(
        string $namespace,
        array $classImports,
        array $functionImports,
        array $constImports,
        ParsedClass $class
    ) {
        $this->namespace = $namespace;
        $this->classImports = $classImports;
        $this->functionImports = $functionImports;
        $this->constImports = $constImports;
        $this->class = $class;
    }
}
