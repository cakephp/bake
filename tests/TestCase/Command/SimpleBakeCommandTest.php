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
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Command;

use Bake\Test\TestCase\TestCase;
use Cake\Console\CommandInterface;
use Cake\Core\Plugin;

/**
 * SimpleBakeCommandTest class
 */
class SimpleBakeCommandTest extends TestCase
{
    /**
     * setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS
            . 'Plugin' . DS . 'Simple' . DS;
        $this->useCommandRunner();
        $this->setAppNamespace('Bake\Test\App');
    }

    /**
     * Test the main method.
     *
     * @return void
     */
    public function testMain()
    {
        $this->generatedFiles = [
            APP . 'Model/Behavior/ExampleBehavior.php',
            ROOT . 'tests/TestCase/Model/Behavior/ExampleBehaviorTest.php',
        ];
        $this->exec('bake behavior Example');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains('class ExampleBehavior extends Behavior', $this->generatedFiles[0]);
    }

    /**
     * Test generating code.
     *
     * @return void
     */
    public function testBake()
    {
        $this->generatedFiles = [
            APP . 'Model/Behavior/ExampleBehavior.php',
            ROOT . 'tests/TestCase/Model/Behavior/ExampleBehaviorTest.php',
        ];
        $this->exec('bake behavior Example');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);

        $result = file_get_contents($this->generatedFiles[0]);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Test the no-test option.
     *
     * @return void
     */
    public function testBakeTestNoTest()
    {
        $this->generatedFile = APP . 'Model/Behavior/ExampleBehavior.php';
        $this->exec('bake behavior --no-test Example');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileDoesNotExist(ROOT . 'tests/TestCase/Model/Behavior/ExampleBehaviorTest.php');
        $this->assertFileContains('class ExampleBehavior extends Behavior', $this->generatedFile);
    }

    public function testBakeWithTheme()
    {
        $this->_loadTestPlugin('TestBakeTheme');

        $this->generatedFile = APP . 'Model/Behavior/FooBarBehavior.php';
        $this->exec('bake behavior --no-test --theme TestBakeTheme FooBar');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileContains('This file was generated using TestBakeTheme theme', $this->generatedFile);
    }

    /**
     * Test baking within a plugin.
     *
     * @return void
     */
    public function testBakePlugin()
    {
        $this->_loadTestPlugin('TestBake');
        $path = Plugin::path('TestBake');

        $this->generatedFiles = [
            $path . 'src/Model/Behavior/ExampleBehavior.php',
            $path . 'tests/TestCase/Model/Behavior/ExampleBehaviorTest.php',
        ];
        $this->exec('bake behavior TestBake.Example');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);

        $result = file_get_contents($this->generatedFiles[0]);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Provider for subclasses.
     *
     * @return string[][]
     */
    public function subclassProvider()
    {
        return [
            ['Bake\Command\BehaviorCommand'],
            ['Bake\Command\ComponentCommand'],
            ['Bake\Command\FormCommand'],
            ['Bake\Command\HelperCommand'],
            ['Bake\Command\MiddlewareCommand'],
            ['Bake\Command\ShellHelperCommand'],
        ];
    }

    /**
     * Test that the various implementations are sane.
     *
     * @dataProvider subclassProvider
     * @return void
     */
    public function testImplementations($class)
    {
        $task = new $class();
        $this->assertIsString($task->name());
        $this->assertIsString($task->fileName('Example'));
        $this->assertIsString($task->template());
    }
}
