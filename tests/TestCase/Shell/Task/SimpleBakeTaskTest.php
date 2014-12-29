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

use Bake\Shell\Task\TemplateTask;
use Bake\Test\TestCase\TestCase;
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
        $io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Task = $this->getMock(
            'Bake\Shell\Task\SimpleBakeTask',
            ['in', 'err', 'createFile', '_stop', 'name', 'template', 'fileName'],
            [$io]
        );
        $this->Task->Test = $this->getMock(
            'Bake\Shell\Task\TestTask',
            [],
            [$io]
        );
        $this->Task->Template = new TemplateTask($io);
        $this->Task->Template->initialize();
        $this->Task->Template->interactive = false;

        $this->Task->pathFragment = 'Model/Behavior/';

        $this->Task->expects($this->any())
            ->method('name')
            ->will($this->returnValue('behavior'));

        $this->Task->expects($this->any())
            ->method('template')
            ->will($this->returnValue('Model/behavior'));

        $this->Task->expects($this->any())
            ->method('fileName')
            ->will($this->returnValue('ExampleBehavior.php'));
    }

    /**
     * Test the main method.
     *
     * @return void
     */
    public function testMain()
    {
        $this->Task->expects($this->once())
            ->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Model/Behavior/ExampleBehavior.php'),
                $this->stringContains('class ExampleBehavior extends Behavior')
            );
        $this->Task->Test->expects($this->once())
            ->method('bake')
            ->with('behavior', 'Example');

        $this->Task->main('Example');
    }

    /**
     * Test the main with plugin.name method.
     *
     * @return void
     */
    public function testMainWithPlugin()
    {
        Plugin::load('SimpleBakeTest', ['path' => APP . 'Plugin' . DS . 'SimpleBakeTest' . DS]);
        $filename = $this->_normalizePath(APP . 'Plugin/SimpleBakeTest/src/Model/Behavior/ExampleBehavior.php');
        $this->Task->expects($this->once())
            ->method('createFile')
            ->with(
                $filename,
                $this->stringContains('class ExampleBehavior extends Behavior')
            );
        $this->Task->Test->expects($this->once())
            ->method('bake')
            ->with('behavior', 'Example');

        $this->Task->main('SimpleBakeTest.Example');
    }

    /**
     * Test generating code.
     *
     * @return void
     */
    public function testBake()
    {
        Configure::write('App.namespace', 'Bake\Test\App');

        $this->Task->expects($this->once())
            ->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Model/Behavior/ExampleBehavior.php'),
                $this->stringContains('class ExampleBehavior extends Behavior')
            );

        $result = $this->Task->bake('Example');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Test bakeTest
     *
     * @return void
     */
    public function testBakeTest()
    {
        $this->Task->plugin = 'TestBake';
        $this->Task->Test->expects($this->once())
            ->method('bake')
            ->with('behavior', 'Example');

        $this->Task->bakeTest('Example');
        $this->assertEquals($this->Task->plugin, $this->Task->Test->plugin);
    }

    /**
     * Test the no-test option.
     *
     * @return void
     */
    public function testBakeTestNoTest()
    {
        $this->Task->params['no-test'] = true;
        $this->Task->Test->expects($this->never())
            ->method('bake');

        $result = $this->Task->bakeTest('Example');
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

        $this->Task->plugin = 'TestBake';
        $this->Task->expects($this->once())
            ->method('createFile')
            ->with(
                $this->_normalizePath($path . 'src/Model/Behavior/ExampleBehavior.php'),
                $this->stringContains('class ExampleBehavior extends Behavior')
            );

        $result = $this->Task->bake('Example');
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
            ['Bake\Shell\Task\HelperTask'],
            ['Bake\Shell\Task\ShellTask'],
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
        $io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);
        $task = new $class($io);
        $this->assertInternalType('string', $task->name());
        $this->assertInternalType('string', $task->fileName('Example'));
        $this->assertInternalType('string', $task->template());
    }
}
