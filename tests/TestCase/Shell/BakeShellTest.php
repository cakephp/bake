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
namespace Bake\Test\TestCase\Shell;

use Bake\Test\TestCase\TestCase;
use Cake\Console\ConsoleIo;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\TestSuite\Stub\ConsoleOutput;

class BakeShellTest extends TestCase
{
    /**
     * fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.Comments',
    ];

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

        $this->setAppNamespace('Bake\Test\App');
        $this->removePlugins(['BakeTest']);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->removePlugins(['BakeTest']);
        unset($this->Shell);
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
            '- controller',
            '- custom_controller',
            '- template',
            '- twig_template',
            '',
            'By using <info>`cake bake [name]`</info> you can invoke a specific bake task.',
        ];

        $this->assertOutputContains(implode(PHP_EOL, $expected));
    }

    /**
     * Test loading tasks from app directories.
     *
     * @return void
     */
    public function testLoadTasksFromApp()
    {
        $this->Shell->loadTasks();
        $expected = [
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
        $this->loadPlugins(['Pastry/PastryTest' => [
            'path' => Configure::read('App.paths.plugins')[0] . 'PastryTest' . DS,
        ]]);

        $this->Shell->loadTasks();
        $this->assertContains('Pastry/PastryTest.ApplePie', $this->Shell->tasks);

        $this->exec('bake');
        $this->assertOutputContains('apple_pie');
    }
}
