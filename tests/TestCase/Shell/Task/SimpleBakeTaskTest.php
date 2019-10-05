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
namespace Bake\Test\TestCase\Shell\Task;

use Bake\Test\TestCase\TestCase;
use Cake\Console\Shell;
use Cake\Core\Plugin;

/**
 * SimpleBakeTaskTest class
 *
 * This test ensures backwards compatibility with userland bake tasks
 * that extend SimpleBakeTask
 */
class SimpleBakeTaskTest extends TestCase
{
    /**
     * setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Simple' . DS;
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
            APP . 'Controller/ExampleCustomController.php',
        ];
        $this->exec('bake custom_controller --no-test Example');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains('class ExampleController extends AppController', $this->generatedFiles[0]);
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
            $path . 'src/Controller/ExampleCustomController.php',
        ];
        $this->exec('bake custom_controller --no-test TestBake.Example');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);

        $this->assertFileContains('namespace TestBake\Controller;', $this->generatedFiles[0]);
    }
}
