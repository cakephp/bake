<?php
declare(strict_types=1);
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
namespace Bake\Test\TestCase\Command;

use Bake\Command\PluginCommand;
use Bake\Test\TestCase\TestCase;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\Exception\StopException;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Filesystem\Folder;

/**
 * PluginCommand Test
 */
class PluginCommandTest extends TestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Plugin' . DS;
        $this->setAppNamespace('Bake\Test\App');
        $this->useCommandRunner();

        // Output into a safe place.
        $path = TMP . 'plugin_task' . DS;
        Configure::write('App.paths.plugins', [$path]);

        // Create the test output path
        mkdir($path, 0777, true);
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
        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertPluginContents('SimpleExample');
    }

    /**
     * test creating a plugin with a custom app namespace.
     *
     * @return void
     */
    public function testMainCustomAppNamespace()
    {
        $this->exec('bake plugin Simple', ['y']);
        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertPluginContents('Simple');

        $bakedRoot = App::path('Plugin')[0];
        $appController = $bakedRoot . 'Simple/src/Controller/AppController.php';
        $this->assertFileContains('use Bake\Test\App\Controller\AppController', $appController);
    }

    /**
     * test generating a plugin with vendor plugin
     *
     * @return void
     */
    public function testMainVendorName()
    {
        $this->exec('bake plugin Company/Example', ['y']);
        $this->assertExitCode(Command::CODE_SUCCESS);
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
        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->assertExitCode(Command::CODE_ERROR);
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

        $this->assertExitCode(Command::CODE_SUCCESS);

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
        $io = $this->createMock(ConsoleIo::class);
        $paths = App::path('Plugin');

        array_unshift($paths, '/fake/path');
        $paths[] = '/fake/path2';

        $command = new PluginCommand();
        $command->path = TMP . 'tests' . DS;
        $result = $command->findPath($paths, $io);

        $this->assertNull($result, 'no return');
        $this->assertEquals(TMP . 'plugin_task' . DS, $command->path);
    }

    /**
     * Test that findPath throws RunTimeException when no
     * path exists for plugins
     *
     * @return void
     */
    public function testFindPathEmpty()
    {
        $this->expectException(StopException::class);
        $io = $this->createMock(ConsoleIo::class);
        $paths = ['/fake/path', '/fake/path2'];

        $command = new PluginCommand();
        $command->path = TMP . 'tests' . DS;

        $command->findPath($paths, $io);
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
