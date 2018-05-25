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
 * @since         1.7.4
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Shell\Task;

use Bake\Test\TestCase\TestCase;
use Cake\Console\Shell;
use Cake\Core\Plugin;

/**
 * CellTaskTest class
 */
class CommandTaskTest extends TestCase
{
    /**
     * setup method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Command' . DS;
    }

    /**
     * Test the excute method.
     *
     * @return void
     */
    public function testMain()
    {
        $this->generatedFiles = [
            APP . 'Command/ExampleCommand.php',
            ROOT . 'tests/TestCase/Command/ExampleCommandTest.php',
        ];
        $this->exec('bake command Example');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains('class ExampleCommand extends Command', $this->generatedFiles[0]);
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
            $path . 'src/Command/ExampleCommand.php',
            $path . 'tests/TestCase/Command/ExampleCommandTest.php',
        ];
        $this->exec('bake command TestBake.Example');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileContains('namespace TestBake\Command;', $this->generatedFiles[0]);
        $this->assertFileContains('class ExampleCommand extends Command', $this->generatedFiles[0]);
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
            $path . 'src/Command/ExampleCommand.php',
            $path . 'tests/TestCase/Command/ExampleCommandTest.php',
        ];
        $this->exec('bake command TestBake.Example');

        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }
}
