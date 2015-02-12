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
use Cake\Core\Configure;
use Cake\Core\Plugin;

/**
 * CellTaskTest class
 */
class CellTaskTest extends TestCase
{
    /**
     * setup method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Cell' . DS;
        $io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Task = $this->getMock(
            'Bake\Shell\Task\CellTask',
            ['in', 'err', 'createFile', '_stop'],
            [$io]
        );
        $this->Task->Test = $this->getMock(
            'Bake\Shell\Task\TestTask',
            [],
            [$io]
        );
        $this->Task->BakeTemplate = new BakeTemplateTask($io);
        $this->Task->BakeTemplate->initialize();
        $this->Task->BakeTemplate->interactive = false;
    }

    /**
     * Test the excute method.
     *
     * @return void
     */
    public function testMain()
    {
        $this->Task->Test->expects($this->once())
            ->method('bake')
            ->with('cell', 'Example');

        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Template/Cell/Example/display.ctp'),
                ''
            );
        $this->Task->expects($this->at(1))
            ->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'View/Cell/ExampleCell.php'),
                $this->stringContains('class ExampleCell extends Cell')
            );

        $this->Task->main('Example');
    }

    /**
     * Test main within a plugin.
     *
     * @return void
     */
    public function testMainPlugin()
    {
        $this->_loadTestPlugin('TestBake');
        $path = Plugin::path('TestBake');

        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with(
                $this->_normalizePath($path . 'src/Template/Cell/Example/display.ctp'),
                ''
            );
        $this->Task->expects($this->at(1))
            ->method('createFile')
            ->with(
                $this->_normalizePath($path . 'src/View/Cell/ExampleCell.php'),
                $this->stringContains('class ExampleCell extends Cell')
            );

        $this->Task->main('TestBake.Example');
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
        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with(
                $this->_normalizePath($path . 'src/Template/Cell/Example/display.ctp'),
                ''
            );
        $this->Task->expects($this->at(1))
            ->method('createFile')
            ->with(
                $this->_normalizePath($path . 'src/View/Cell/ExampleCell.php'),
                $this->stringContains('class ExampleCell extends Cell')
            );

        $result = $this->Task->bake('Example');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }
}
