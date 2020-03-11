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
 * @since         1.2.2
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Command;

use Bake\Test\TestCase\TestCase;
use Cake\Command\Command;
use Cake\Core\Plugin;

/**
 * TaskCommand test
 */
class TaskCommandTest extends TestCase
{
    /**
     * setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Shell' . DS . 'Task' . DS;
        $this->setAppNamespace('Bake\Test\App');
        $this->useCommandRunner();
    }

    /**
     * Test the main method.
     *
     * @return void
     */
    public function testMain()
    {
        $this->generatedFiles = [
            APP . 'Shell/Task/ExampleTask.php',
            ROOT . 'tests/TestCase/Shell/Task/ExampleTaskTest.php',
        ];
        $this->exec('bake task Example');

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains('namespace Bake\Test\App\Shell\Task', $this->generatedFiles[0]);
        $this->assertFileContains('class ExampleTask extends Shell', $this->generatedFiles[0]);
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

        $this->generatedFiles = [
            $path . 'src/Shell/Task/ExampleTask.php',
            $path . 'tests/TestCase/Shell/Task/ExampleTaskTest.php',
        ];
        $this->exec('bake task TestBake.Example');

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains('namespace TestBake\Shell\Task', $this->generatedFiles[0]);
        $this->assertFileContains('class ExampleTask extends Shell', $this->generatedFiles[0]);
    }
}
