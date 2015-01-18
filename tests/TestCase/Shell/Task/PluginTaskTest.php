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

use Bake\Shell\Task\ProjectTask;
use Bake\Shell\Task\TemplateTask;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\TestCase;

/**
 * PluginTaskPlugin class
 */
class PluginTaskTest extends TestCase
{
    use StringCompareTrait;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Plugin' . DS;
        $this->io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Task = $this->getMock(
            'Bake\Shell\Task\PluginTask',
            ['in', 'err', '_stop', 'clear', 'callProcess', '_rootComposerFilePath'],
            [$this->io]
        );

        $this->Task->Template = new TemplateTask($this->io);
        $this->Task->Template->interactive = false;

        $this->Task->path = TMP . 'tests' . DS . 'BakedPlugins' . DS;
        new Folder($this->Task->path, true);

        $this->Task->bootstrap = TMP . 'tests' . DS . 'bootstrap.php';
        touch($this->Task->bootstrap);

        $this->_path = App::path('Plugin');
    }

    /**
     * tearDown()
     *
     * @return void
     */
    public function tearDown()
    {
        $Folder = new Folder(TMP . 'tests' . DS . 'BakedPlugins');
        $Folder->delete();

        parent::tearDown();
    }

    /**
     * test bake()
     *
     * @return void
     */
    public function testBake()
    {
        $this->Task->expects($this->at(0))->method('in')
            ->will($this->returnValue('y'));

        $this->Task->bake('SimpleExample');
        $this->assertPluginContents('SimpleExample');
    }

    /**
     * Test the main method
     *
     * @return void
     */
    public function testMain()
    {
        $this->Task->expects($this->at(0))->method('in')
            ->will($this->returnValue('y'));

        $this->Task->main('SimpleExample');
        $this->assertPluginContents('SimpleExample');
    }

    /**
     * With no args, main should do nothing
     *
     * @return void
     */
    public function testMainWithNoArgs()
    {
        $this->Task->expects($this->at(0))
            ->method('err')
            ->with($this->stringContains('You must'));

        $this->Task->main();
    }

    /**
     * Test that baking a plugin for a project that contains a composer.json, the later
     * will be updated
     *
     * @return void
     */
    public function testMainUpdateComposer()
    {
        $this->Task->expects($this->at(0))->method('in')
            ->will($this->returnValue('y'));

        $this->io->expects($this->any())
            ->method('askChoice')
            ->will($this->returnValue('y'));

        $this->Task->Project = $this->getMock('ComposerProject', ['findComposer']);
        $this->Task->Project->expects($this->at(0))
            ->method('findComposer')
            ->will($this->returnValue('composer.phar'));

        $file = TMP . 'tests' . DS . 'main-composer.json';
        file_put_contents($file, '{}');

        $savePath = $this->Task->path;

        $this->Task->path = ROOT . DS . 'tests' . DS . 'BakedPlugins/';

        $this->Task->expects($this->any())
            ->method('_rootComposerFilePath')
            ->will($this->returnValue($file));

        $this->Task->expects($this->once())
            ->method('callProcess')
            ->with('php ' . escapeshellarg('composer.phar') . ' dump-autoload');

        $this->Task->main('ComposerExample');

        $result = file_get_contents($file);
        $this->assertSameAsFile(__FUNCTION__ . '.json', $result);

        $folder = new Folder($this->Task->path);
        $folder->delete();

        $this->Task->path = $savePath;
    }

    /**
     * Test that findPath ignores paths that don't exist.
     *
     * @return void
     */
    public function testFindPathNonExistent()
    {
        $paths = App::path('Plugin');

        array_unshift($paths, '/fake/path');
        $paths[] = '/fake/path2';

        $this->Task = $this->getMock(
            'Bake\Shell\Task\PluginTask',
            ['in', 'out', 'err', '_stop'],
            [$this->io]
        );
        $this->Task->path = TMP . 'tests' . DS;

        $this->Task->method('findPath')
            ->will($this->returnValue($paths[0]));

        $this->Task->findPath($paths);
    }

    /**
     * Test that findPath throws RunTimeException when no
     * path exists for plugins
     *
     * @expectedException \RunTimeException
     * @return void
     */
    public function testFindPathEmpty()
    {
        $paths = ['/fake/path', '/fake/path2'];

        $this->Task = $this->getMock(
            'Bake\Shell\Task\PluginTask',
            ['in', 'out', 'err', '_stop'],
            [$this->io]
        );
        $this->Task->path = TMP . 'tests' . DS;

        $this->Task->findPath($paths);
    }

    /**
     * Check the baked plugin matches the expected output
     *
     * Compare to a static copy of the plugin in the comparison folder
     *
     * @param string $pluginName the name of the plugin to compare to
     * @return void
     */
    public function assertPluginContents($pluginName)
    {
        $comparisonRoot = $this->_compareBasePath . $pluginName . DS;
        $comparisonDir = new Folder($comparisonRoot);
        $comparisonFiles = $comparisonDir->findRecursive();

        $bakedRoot = $this->Task->path . $pluginName . DS;
        $bakedDir = new Folder($bakedRoot);
        $bakedFiles = $comparisonDir->findRecursive();

        $this->assertSame(
            count($comparisonFiles),
            count($bakedFiles),
            'A different number of files were created than expected'
        );

        foreach ($comparisonFiles as $file) {
            $file = substr($file, strlen($comparisonRoot));
            $result = file_get_contents($bakedRoot . $file);
            $this->assertSameAsFile($pluginName . DS . $file, $result);
        }
    }
}
