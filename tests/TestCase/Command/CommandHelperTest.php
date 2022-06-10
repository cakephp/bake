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
 * @since         2.6.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Command;

use Bake\Test\TestCase\TestCase;
use Cake\Console\CommandInterface;
use Cake\Core\Plugin;

/**
 * CommandHelperTest class
 */
class CommandHelperTest extends TestCase
{
    /**
     * setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Command' . DS;
        $this->setAppNamespace('Bake\Test\App');
        $this->useCommandRunner();
    }

    /**
     * Test the excute method.
     *
     * @return void
     */
    public function testBakeCommandHelper()
    {
        $this->generatedFiles = [
            APP . 'Command/Helper/ErrorHelper.php',
            ROOT . 'tests/TestCase/Command/Helper/ErrorHelperTest.php',
        ];
        $this->exec('bake command_helper Error');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));

        $testsPath = dirname($this->_compareBasePath) . DS . 'Test' . DS;
        $this->assertSameAsFile($testsPath . __FUNCTION__ . 'Test.php', file_get_contents($this->generatedFiles[1]));
    }

    /**
     * Test the excute method with plugin
     *
     * @return void
     */
    public function testBakeCommandHelperPlugin()
    {
        $this->_loadTestPlugin('TestBake');
        $path = Plugin::path('TestBake');

        $this->generatedFiles = [
            $path . 'src/Command/Helper/ErrorHelper.php',
            $path . 'tests/TestCase/Command/Helper/ErrorHelperTest.php',
        ];
        $this->exec('bake command_helper TestBake.Error');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }
}
