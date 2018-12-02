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
    }

    /**
     * Test the excute method.
     *
     * @return void
     */
    public function testMain()
    {
        $this->generatedFiles = [
            APP . 'View/Cell/ExampleCell.php',
            ROOT . 'tests/TestCase/View/Cell/ExampleCellTest.php',
            APP . 'Template/Cell/Example/display.ctp',
        ];
        $this->exec('bake cell Example');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains('class ExampleCell extends Cell', $this->generatedFiles[0]);
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
            $path . 'src/View/Cell/ExampleCell.php',
            $path . 'tests/TestCase/View/Cell/ExampleCellTest.php',
            $path . 'src/Template/Cell/Example/display.ctp',
        ];
        $this->exec('bake cell TestBake.Example');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileContains('namespace TestBake\View\Cell;', $this->generatedFiles[0]);
        $this->assertFileContains('class ExampleCell extends Cell', $this->generatedFiles[0]);
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
            $path . 'src/View/Cell/ExampleCell.php',
            $path . 'tests/TestCase/View/Cell/ExampleCellTest.php',
            $path . 'src/Template/Cell/Example/display.ctp',
        ];
        $this->exec('bake cell TestBake.Example');

        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }

    /**
     * Test the excute method with prefix.
     *
     * @return void
     */
    public function testMainPrefix()
    {
        $this->generatedFiles = [
            APP . 'View/Cell/Admin/ExampleCell.php',
            ROOT . 'tests/TestCase/View/Cell/Admin/ExampleCellTest.php',
            APP . 'Template/Cell/Admin/Example/display.ctp',
        ];
        $this->exec('bake cell --prefix Admin Example');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains('namespace App\View\Cell\Admin;', $this->generatedFiles[0]);
        $this->assertFileContains('class ExampleCell extends Cell', $this->generatedFiles[0]);
    }

    /**
     * Test main within a prefix and plugin.
     *
     * @return void
     */
    public function testMainPrefixPlugin()
    {
        $this->_loadTestPlugin('TestBake');
        $path = Plugin::path('TestBake');

        $this->generatedFiles = [
            $path . 'src/View/Cell/Admin/ExampleCell.php',
            $path . 'tests/TestCase/View/Cell/Admin/ExampleCellTest.php',
            $path . 'src/Template/Cell/Admin/Example/display.ctp',
        ];
        $this->exec('bake cell --prefix Admin TestBake.Example');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileContains('namespace TestBake\View\Cell\Admin;', $this->generatedFiles[0]);
        $this->assertFileContains('class ExampleCell extends Cell', $this->generatedFiles[0]);
    }
}
