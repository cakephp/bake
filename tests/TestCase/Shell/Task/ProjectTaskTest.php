<?php
/**
 * CakePHP : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Shell\Task;

use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\TestSuite\TestCase;

/**
 * ProjectTask Test class
 *
 */
class ProjectTaskTest extends TestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Task = $this->getMock(
            'Bake\Shell\Task\ProjectTask',
            ['in', 'err', 'callProcess', 'createFile', '_stop'],
            [$io]
        );
        $this->Task->path = TMP;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        $Folder = new Folder($this->Task->path . 'BakeTestApp');
        $Folder->delete();
        unset($this->Task);
    }

    /**
     * creates a test project that is used for testing project task.
     *
     * @return void
     */
    protected function _setupTestProject()
    {
        $this->Task->expects($this->at(0))->method('in')->will($this->returnValue('y'));
        $this->Task->bake($this->Task->path . 'BakeTestApp');
    }

    /**
     * test bake with an absolute path.
     *
     * @return void
     */
    public function testExecuteWithAbsolutePath()
    {
        $this->Task->method('in')
            ->will($this->returnValue('y'));
        $this->Task->expects($this->once())
            ->method('callProcess')
            ->with($this->stringContains('create-project -s dev cakephp/app'));

        $this->Task->args = [TMP . 'BakeProject'];
        $this->Task->main();
    }
}
