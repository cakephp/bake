<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.1.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Command;

use Bake\Test\TestCase\TestCase;
use Cake\Console\CommandInterface;
use Cake\Core\Plugin;

/**
 * MailerCommandTest class
 */
class MailerCommandTest extends TestCase
{
    /**
     * setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Mailer' . DS;
        $this->setAppNamespace('Bake\Test\App');
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
        ];
        $this->exec('bake mailer Example');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
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
        ];
        $this->exec('bake mailer TestBake.Example');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
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
        $templatePath = Plugin::templatePath('TestBake');

        $this->generatedFiles = [
            $path . 'src/Mailer/ExampleMailer.php',
            $path . 'tests/TestCase/Mailer/ExampleMailerTest.php',
            $templatePath . 'layout/email/html/example.php',
            $templatePath . 'layout/email/text/example.php',
        ];
        $this->exec('bake mailer TestBake.Example');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }
}
