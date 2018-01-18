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
 * @since         1.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Shell\Task;

use Bake\Shell\Task\BakeTemplateTask;
use Bake\Test\TestCase\TestCase;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\Plugin;

/**
 * MailerTaskTest class
 */
class MailerTaskTest extends TestCase
{
    /**
     * setup method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Mailer' . DS;
    }

    /**
     * Test the excute method.
     *
     * @return void
     */
    public function testMain()
    {
        $this->generatedFiles = [
            APP . 'Mailer/ExampleMailer.php',
            ROOT . 'tests/TestCase/Mailer/ExampleMailerTest.php',
            APP . 'Template/Layout/Email/html/example.ctp',
            APP . 'Template/Layout/Email/text/example.ctp',
        ];
        $this->exec('bake mailer Example');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles, 'files should be created');
        $this->assertFileContains('class ExampleMailer extends Mailer', $this->generatedFiles[0]);
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
            $path . 'src/Mailer/ExampleMailer.php',
            $path . 'tests/TestCase/Mailer/ExampleMailerTest.php',
            $path . 'src/Template/Layout/Email/html/example.ctp',
            $path . 'src/Template/Layout/Email/text/example.ctp',
        ];
        $this->exec('bake mailer TestBake.Example');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles, 'files should be created');
        $this->assertFileContains('namespace TestBake\Mailer;', $this->generatedFiles[0]);
        $this->assertFileContains('class ExampleMailer extends Mailer', $this->generatedFiles[0]);
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
            $path . 'src/Mailer/ExampleMailer.php',
            $path . 'tests/TestCase/Mailer/ExampleMailerTest.php',
            $path . 'src/Template/Layout/Email/html/example.ctp',
            $path . 'src/Template/Layout/Email/text/example.ctp',
        ];
        $this->exec('bake mailer TestBake.Example');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }
}
