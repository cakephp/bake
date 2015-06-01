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
namespace Bake\Test\TestCase\Shell;

use Bake\Test\TestCase\TestCase;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Shell\BakeShellShell;

class BakeShellTest extends TestCase
{
    /**
     * fixtures
     *
     * @var array
     */
    public $fixtures = ['core.comments'];

    /**
     * setup test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Shell = $this->getMock(
            'Bake\Shell\BakeShell',
            ['in', 'out', 'hr', 'err', 'createFile', '_stop'],
            [$this->io]
        );
        Configure::write('App.namespace', 'Bake\Test\App');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->Shell);
    }

    /**
     * test bake all
     *
     * @return void
     */
    public function testAllWithModelName()
    {
        $this->Shell->Model = $this->getMock('Bake\Shell\Task\ModelTask');
        $this->Shell->Controller = $this->getMock('Bake\Shell\Task\ControllerTask');
        $this->Shell->Template = $this->getMock('Bake\Shell\Task\TemplateTask');

        $this->Shell->Model->expects($this->once())
            ->method('main')
            ->with('Comments')
            ->will($this->returnValue(true));

        $this->Shell->Controller->expects($this->once())
            ->method('main')
            ->with('Comments')
            ->will($this->returnValue(true));

        $this->Shell->Template->expects($this->once())
            ->method('main')
            ->with('Comments');

        $this->Shell->expects($this->at(0))
            ->method('out')
            ->with('Bake All');

        $this->Shell->expects($this->at(2))
            ->method('out')
            ->with('<success>Bake All complete.</success>');

        $this->Shell->connection = '';
        $this->Shell->params = [];
        $this->Shell->all('Comments');
    }

    /**
     * Test the main function.
     *
     * @return void
     */
    public function testMain()
    {
        $this->Shell->expects($this->at(0))
            ->method('out')
            ->with($this->stringContains('The following commands'));

        $this->Shell->expects($this->exactly(17))
            ->method('out');

        $this->Shell->loadTasks();
        $this->Shell->main();
    }

    /**
     * Test that the generated option parser reflects all tasks.
     *
     * @return void
     */
    public function testGetOptionParser()
    {
        $this->Shell->loadTasks();
        $parser = $this->Shell->getOptionParser();
        $commands = $parser->subcommands();
        $this->assertArrayHasKey('fixture', $commands);
        $this->assertArrayHasKey('template', $commands);
        $this->assertArrayHasKey('controller', $commands);
        $this->assertArrayHasKey('model', $commands);
    }

    /**
     * Test loading tasks from core directories.
     *
     * @return void
     */
    public function testLoadTasksCoreAndApp()
    {
        $this->Shell->loadTasks();
        $expected = [
            'Bake.Behavior',
            'Bake.Cell',
            'Bake.Component',
            'Bake.Controller',
            'Bake.Fixture',
            'Bake.Form',
            'Bake.Helper',
            'Bake.Model',
            'Bake.Plugin',
            'Bake.Shell',
            'Bake.Test',
            'Bake.Template'
        ];
        sort($this->Shell->tasks);
        sort($expected);
        $this->assertEquals($expected, $this->Shell->tasks);
    }

    /**
     * Test loading tasks from plugins
     *
     * @return void
     */
    public function testLoadTasksPlugin()
    {
        $this->_loadTestPlugin('BakeTest');
        $this->Shell->loadTasks();
        $this->assertContains('BakeTest.Widget', $this->Shell->tasks);
        $this->assertContains('BakeTest.Zerg', $this->Shell->tasks);
    }
}
