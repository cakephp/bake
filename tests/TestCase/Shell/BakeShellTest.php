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
use Cake\Console\ConsoleIo;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\TestSuite\Stub\ConsoleOutput;

class BakeShellTest extends TestCase
{
    /**
     * fixtures
     *
     * @var array
     */
    public $fixtures = ['core.Comments'];

    /**
     * @var ConsoleOutput
     */
    protected $out;

    /**
     * @var ConsoleIo
     */
    protected $io;

    /**
     * @var \Bake\Shell\BakeShell|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $Shell;

    /**
     * setup test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->out = new ConsoleOutput();
        $this->io = new ConsoleIo($this->out, $this->out);

        $this->Shell = $this->getMockBuilder('Bake\Shell\BakeShell')
            ->setMethods(['in', 'createFile', '_stop'])
            ->setConstructorArgs([$this->io])
            ->getMock();

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
        $this->Shell->Model = $this->getMockBuilder('Bake\Shell\Task\ModelTask')
            ->setMethods(['main'])
            ->getMock();
        $this->Shell->Controller = $this->getMockBuilder('Bake\Shell\Task\ControllerTask')
            ->setMethods(['main'])
            ->getMock();
        $this->Shell->Template = $this->getMockBuilder('Bake\Shell\Task\TemplateTask')
            ->setMethods(['main'])
            ->getMock();

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

        $this->Shell->connection = '';
        $this->Shell->params = ['prefix' => 'account'];
        $this->Shell->all('Comments');

        $output = $this->out->messages();

        $expected = [
            'Bake All',
            '---------------------------------------------------------------',
            '<success>Bake All complete.</success>',
        ];
        $this->assertSame($expected, $output);
    }

    /**
     * test bake all --everything [--connection default]
     *
     * @return void
     */
    public function testAllEverythingDefault()
    {
        $this->Shell->Model = $this->getMockBuilder('Bake\Shell\Task\ModelTask')
            ->setMethods(['listAll'])
            ->getMock();

        $this->Shell->Model->expects($this->once())
            ->method('listAll')
            ->will($this->returnValue([]));

        $this->Shell->connection = '';
        $this->Shell->params = ['prefix' => 'account', 'everything' => true];
        $this->Shell->all();

        $output = $this->out->messages();

        $expected = [
            'Bake All',
            '---------------------------------------------------------------',
            '<success>Bake All complete.</success>',
        ];
        $this->assertSame($expected, $output);
    }

    /**
     * test bake all --everything --connection test
     *
     * @return void
     */
    public function testAllEverything()
    {
        $this->Shell->Model = $this->getMockBuilder('Bake\Shell\Task\ModelTask')
            ->setMethods(['main'])
            ->getMock();
        $this->Shell->Controller = $this->getMockBuilder('Bake\Shell\Task\ControllerTask')
            ->setMethods(['main'])
            ->getMock();
        $this->Shell->Template = $this->getMockBuilder('Bake\Shell\Task\TemplateTask')
            ->setMethods(['main'])
            ->getMock();

        $this->Shell->Model->expects($this->never())
            ->method('main');

        $this->Shell->Controller->expects($this->never())
            ->method('main');

        $this->Shell->Template->expects($this->never())
            ->method('main');

        $this->Shell->connection = 'test';
        $this->Shell->params = ['prefix' => 'account', 'everything' => true, 'connection' => 'test'];
        $this->Shell->all();

        $output = $this->out->messages();

        $expected = ['<warning>Can only bake everything on default connection</warning>'];
        $this->assertSame($expected, $output);
    }

    /**
     * Test the main function.
     *
     * @return void
     */
    public function testMain()
    {
        $this->exec('bake');
        $this->assertExitCode(Shell::CODE_ERROR);

        $expected = [
            'The following commands can be used to generate skeleton code for your application.',
            '',
            '<info>Available bake commands:</info>',
            '',
            '- all',
            '- behavior',
            '- cell',
            '- command',
            '- component',
            '- controller',
            '- custom_controller',
            '- fixture',
            '- form',
            '- helper',
            '- mailer',
            '- middleware',
            '- model',
            '- plugin',
            '- shell',
            '- shell_helper',
            '- task',
            '- template',
            '- test',
            '- twig_template',
            '',
            'By using <info>`cake bake [name]`</info> you can invoke a specific bake task.',
        ];

        $this->assertOutputContains(implode(PHP_EOL, $expected));
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

        $this->assertNull($parser->options()['theme']->defaultValue());

        Configure::write('Bake.theme', 'Mytheme');
        $parser = $this->Shell->getOptionParser();
        $this->assertSame('Mytheme', $parser->options()['theme']->defaultValue());
        Configure::delete('Bake.theme');
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
            'Bake.Command',
            'Bake.Component',
            'Bake.Fixture',
            'Bake.Form',
            'Bake.Helper',
            'Bake.Mailer',
            'Bake.Middleware',
            'Bake.Model',
            'Bake.Plugin',
            'Bake.Shell',
            'Bake.ShellHelper',
            'Bake.Task',
            'Bake.Test',
            'Bake.Template',
            'Controller',
            'CustomController',
            'WyriHaximus/TwigView.TwigTemplate',
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

    /**
     * Test loading tasks from vendored plugins
     *
     * @return void
     */
    public function testLoadTasksVendoredPlugin()
    {
        $this->loadPlugins([
            'Pastry/PastryTest' => [
                'path' => Configure::read('App.paths.plugins')[0] . 'PastryTest' . DS,
                'autoload' => true,
            ],
        ]);

        $this->Shell->loadTasks();
        $this->assertContains('Pastry/PastryTest.ApplePie', $this->Shell->tasks);

        $this->exec('bake');
        $this->assertOutputContains('apple_pie');
    }

    /**
     * Tests that quiet mode does not ask interactively and does not silently overwrite anywhere.
     * -f would be needed for that.
     *
     * @return void
     */
    public function testBakeAllNonInteractive()
    {
        $this->Shell->loadTasks();

        $path = APP;
        $testsPath = ROOT . 'tests' . DS;

        // We ignore our existing CommentsController test file
        $files = [
            $path . 'Template/Comments/add.ctp',
            $path . 'Template/Comments/edit.ctp',
            $path . 'Template/Comments/index.ctp',
            $path . 'Template/Comments/view.ctp',
            $path . 'Model/Table/CommentsTable.php',
            $path . 'Model/Entity/Comment.php',
            $testsPath . 'Fixture/CommentsFixture.php',
            $testsPath . 'TestCase/Model/Table/CommentsTableTest.php',
            $testsPath . 'TestCase/Controller/CommentsControllerTest.php',
        ];
        foreach ($files as $file) {
            $this->assertFileNotExists($file, 'File should not yet exist before `bake all`.');
        }

        $existingFile = $path . 'Controller/CommentsController.php';
        $this->assertFileExists($existingFile);
        $content = file_get_contents($existingFile);

        $this->Shell->runCommand(['all', 'Comments'], false, ['quiet' => true]);
        $output = $this->out->messages();

        $this->assertContains('<success>Bake All complete.</success>', implode(' ', $output));

        foreach ($files as $file) {
            $this->assertFileExists($file, 'File should exist after `bake all`.');
            unlink($file);
        }

        $this->assertSame($content, file_get_contents($existingFile), 'File got overwritten, but should not have.');
    }
}
