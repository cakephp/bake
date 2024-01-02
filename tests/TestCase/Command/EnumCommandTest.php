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
 * @since         3.1.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Command;

use Bake\Test\TestCase\TestCase;
use Cake\Console\CommandInterface;
use Cake\Core\Plugin;

/**
 * EnumCommandTest class
 */
class EnumCommandTest extends TestCase
{
    /**
     * setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Model' . DS;
        $this->setAppNamespace('Bake\Test\App');
    }

    /**
     * test baking an enum
     *
     * @return void
     */
    public function testBakeEnum()
    {
        $this->generatedFile = APP . 'Model/Enum/FooBar.php';
        $this->exec('bake enum FooBar', ['y']);

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an enum with int return type
     *
     * @return void
     */
    public function testBakeEnumBackedInt()
    {
        $this->generatedFile = APP . 'Model/Enum/FooBar.php';
        $this->exec('bake enum FooBar -i', ['y']);

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an enum with string return type and cases
     *
     * @return void
     */
    public function testBakeEnumBackedWithCases()
    {
        $this->generatedFile = APP . 'Model/Enum/FooBar.php';
        $this->exec('bake enum FooBar foo,bar:b,bar_baz', ['y']);

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an enum with string return type and cases
     *
     * @return void
     */
    public function testBakeEnumBackedIntWithCases()
    {
        $this->generatedFile = APP . 'Model/Enum/FooBar.php';
        $this->exec('bake enum FooBar foo,bar,bar_baz:9 -i', ['y']);

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }
}
