<?php
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
namespace Bake\Test\TestCase\Shell\Task;

use Bake\Shell\Task\BakeTemplateTask;
use Bake\Test\TestCase\TestCase;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\Plugin;

/**
 * SimpleBakeTaskTest class
 */
class SimpleBakeTaskTest extends TestCase
{
    /**
     * setup method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Simple' . DS;
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

        $this->assertExitCode(Shell::CODE_SUCCESS);
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
        Configure::write('App.namespace', 'Bake\Test\App');

        $this->generatedFiles = [
            APP . 'Model/Behavior/ExampleBehavior.php',
            ROOT . 'tests/TestCase/Model/Behavior/ExampleBehaviorTest.php',
        ];
        $this->exec('bake behavior Example');

        $this->assertExitCode(Shell::CODE_SUCCESS);
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

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileNotExists(ROOT . 'tests/TestCase/Model/Behavior/ExampleBehaviorTest.php');
        $this->assertFileContains('class ExampleBehavior extends Behavior', $this->generatedFile);
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

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);

        $result = file_get_contents($this->generatedFiles[0]);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Provider for subclasses.
     *
     * @return array
     */
    public function subclassProvider()
    {
        return [
            ['Bake\Shell\Task\BehaviorTask'],
            ['Bake\Shell\Task\ComponentTask'],
            ['Bake\Shell\Task\FormTask'],
            ['Bake\Shell\Task\HelperTask'],
            ['Bake\Shell\Task\ShellTask'],
            ['Bake\Shell\Task\ShellHelperTask'],
            ['Bake\Shell\Task\TaskTask'],
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
        $io = $this->getMockBuilder('Cake\Console\ConsoleIo')
            ->disableOriginalConstructor()
            ->getMock();
        $task = new $class($io);
        $this->assertInternalType('string', $task->name());
        $this->assertInternalType('string', $task->fileName('Example'));
        $this->assertInternalType('string', $task->template());
    }
}
