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

use Bake\Shell\Task\BakeTemplateTask;
use Bake\Shell\Task\PluginTask;
use Bake\Test\TestCase\TestCase;
use Cake\Console\Shell;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Filesystem\Folder;

/**
 * PluginTaskPlugin class
 */
class PluginTaskTest extends TestCase
{
    /**
     * @var \Cake\Console\ConsoleIo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $io;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Plugin' . DS;

        // Output into a safe place.
        $path = TMP . 'plugin_task' . DS;
        Configure::write('App.paths.plugins', [$path]);

        // Create the test output path
        $folder = new Folder($path);
        $folder->create($path);

        $this->io = $this->getMockBuilder('Cake\Console\ConsoleIo')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * tearDown()
     *
     * @return void
     */
    public function tearDown()
    {
        $folder = new Folder(TMP . 'plugin_task');
        $folder->delete();

        parent::tearDown();
    }

    /**
     * test creating a plugin skeleton
     *
     * @return void
     */
    public function testMainBakePluginContents()
    {
        $this->exec('bake plugin SimpleExample', ['y']);
        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertPluginContents('SimpleExample');
    }

    /**
     * test creating a plugin with a custom app namespace.
     *
     * @return void
     */
    public function testMainCustomAppNamespace()
    {
        Configure::write('App.namespace', 'MyApp');

        $this->exec('bake plugin Simple', ['y']);
        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertPluginContents('Simple');

        $bakedRoot = App::path('Plugin')[0];
        $appController = $bakedRoot . 'Simple/src/Controller/AppController.php';
        $this->assertFileContains('use MyApp\Controller\AppController', $appController);
    }

    /**
     * test generating a plugin with vendor plugin
     *
     * @return void
     */
    public function testMainVendorName()
    {
        $this->exec('bake plugin Company/Example', ['y']);
        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertPluginContents('Company/Example');
    }

    /**
     * test main with vendor plugin and incorrect casing
     *
     * @return void
     */
    public function testMainVendorNameCasingFix()
    {
        $this->exec('bake plugin company/example', ['y']);
        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertPluginContents('Company/Example');
    }

    /**
     * With no args, main should do nothing
     *
     * @return void
     */
    public function testMainWithNoArgs()
    {
        $this->exec('bake plugin');

        $this->assertExitCode(Shell::CODE_ERROR);
        $this->assertErrorContains('You must');
    }

    /**
     * Test that baking a plugin for a project that contains a composer.json, the later
     * will be updated
     *
     * @return void
     */
    public function testMainUpdateComposer()
    {
        $this->skipIf(
            DIRECTORY_SEPARATOR == '\\',
            'Skipping composer test on windows as `which` does not work well.'
        );
        $composerPath = exec('which composer');
        if (!$composerPath && file_exists('./composer.phar')) {
            $composerPath = './composer.phar';
        }
        $this->skipIf(!file_exists($composerPath), 'Cannot find composer.phar.');

        $composerConfig = ROOT . 'composer.json';

        // Seed the composer.json with valid JSON
        file_put_contents($composerConfig, '{}');

        $this->exec("bake plugin --composer '{$composerPath}' composer_example", ['y', 'y']);

        $this->assertExitCode(Shell::CODE_SUCCESS);

        $result = json_decode(file_get_contents(ROOT . 'composer.json'), true);
        $this->assertArrayHasKey('autoload', $result);
        $this->assertArrayHasKey('psr-4', $result['autoload']);
        $this->assertArrayHasKey('ComposerExample\\', $result['autoload']['psr-4']);

        $this->assertArrayHasKey('autoload-dev', $result);
        $this->assertArrayHasKey('psr-4', $result['autoload-dev']);
        $this->assertArrayHasKey('ComposerExample\\Test\\', $result['autoload-dev']['psr-4']);

        $pluginPath = App::path('Plugin')[0];
        $this->assertEquals(
            $pluginPath . 'ComposerExample' . DS . 'src' . DS,
            $result['autoload']['psr-4']['ComposerExample\\']
        );
        $this->assertEquals(
            $pluginPath . 'ComposerExample' . DS . 'tests' . DS,
            $result['autoload-dev']['psr-4']['ComposerExample\\Test\\']
        );

        // Cleanup
        unlink(ROOT . 'composer.json');

        $folder = new Folder(ROOT . 'vendor');
        $folder->delete();
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

        $task = new PluginTask($this->io);
        $task->path = TMP . 'tests' . DS;
        $result = $task->findPath($paths);

        $this->assertNull($result, 'no return');
        $this->assertEquals(TMP . 'plugin_task' . DS, $task->path);
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

        $task = new PluginTask($this->io);
        $task->path = TMP . 'tests' . DS;

        $task->findPath($paths);
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
        $pluginName = str_replace('/', DS, $pluginName);
        $comparisonRoot = $this->_compareBasePath . $pluginName . DS;
        $comparisonDir = new Folder($comparisonRoot);
        $comparisonFiles = $comparisonDir->findRecursive();

        $bakedRoot = App::path('Plugin')[0] . $pluginName . DS;
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
