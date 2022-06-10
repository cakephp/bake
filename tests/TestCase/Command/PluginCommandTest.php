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
use Cake\Console\CommandInterface;
use Cake\Console\ConsoleIo;
use Cake\Console\Exception\StopException;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Filesystem\Filesystem;
use SplFileInfo;

/**
 * PluginCommand Test
 */
class PluginCommandTest extends TestCase
{
    protected $testAppFile = APP . 'Application.php';

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
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

        if (file_exists(APP . 'Application.php.bak')) {
            rename(APP . 'Application.php.bak', APP . 'Application.php');
        } else {
            copy(APP . 'Application.php', APP . 'Application.php.bak');
        }
    }

    /**
     * tearDown()
     *
     * @return void
     */
    public function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->deleteDir(TMP . 'plugin_task');

        if (file_exists(APP . 'Application.php.bak')) {
            rename(APP . 'Application.php.bak', APP . 'Application.php');
        }

        parent::tearDown();
    }

    /**
     * test creating a plugin skeleton
     *
     * @return void
     */
    public function testMainBakePluginContents()
    {
        $this->exec('bake plugin SimpleExample', ['y', 'n']);
        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertPluginContents('SimpleExample');
    }

    /**
     * test creating a plugin with a custom app namespace.
     *
     * @return void
     */
    public function testMainCustomAppNamespace()
    {
        $this->exec('bake plugin Simple', ['y', 'n']);
        $this->assertExitCode(CommandInterface::CODE_SUCCESS);

        $bakedRoot = App::path('plugins')[0];
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
        $this->exec('bake plugin Company/Example', ['y', 'n']);
        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertPluginContents('Company/Example');
    }

    /**
     * test main with vendor plugin and incorrect casing
     *
     * @return void
     */
    public function testMainVendorNameCasingFix()
    {
        $this->exec('bake plugin company/example', ['y', 'n']);
        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
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

        $this->assertExitCode(CommandInterface::CODE_ERROR);
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
        copy($composerConfig, ROOT . 'composer.json.bak');

        $this->exec("bake plugin --composer '{$composerPath}' composer_example", ['y', 'y']);

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);

        $result = json_decode(file_get_contents(ROOT . 'composer.json'), true);
        $this->assertArrayHasKey('autoload', $result);
        $this->assertArrayHasKey('psr-4', $result['autoload']);
        $this->assertArrayHasKey('ComposerExample\\', $result['autoload']['psr-4']);

        $this->assertArrayHasKey('autoload-dev', $result);
        $this->assertArrayHasKey('psr-4', $result['autoload-dev']);
        $this->assertArrayHasKey('ComposerExample\\Test\\', $result['autoload-dev']['psr-4']);

        $pluginPath = App::path('plugins')[0];
        $this->assertSame(
            $pluginPath . 'ComposerExample' . DS . 'src' . DS,
            $result['autoload']['psr-4']['ComposerExample\\']
        );
        $this->assertSame(
            $pluginPath . 'ComposerExample' . DS . 'tests' . DS,
            $result['autoload-dev']['psr-4']['ComposerExample\\Test\\']
        );

        // Restore
        copy(ROOT . 'composer.json.bak', $composerConfig);
        unlink(ROOT . 'composer.json.bak');

        $fs = new Filesystem();
        $fs->deleteDir(ROOT . 'vendor');
    }

    /**
     * Test that findPath ignores paths that don't exist.
     *
     * @return void
     */
    public function testFindPathNonExistent()
    {
        $io = $this->createMock(ConsoleIo::class);
        $paths = App::path('plugins');

        array_unshift($paths, '/fake/path');
        $paths[] = '/fake/path2';

        $command = new PluginCommand();
        $command->path = TMP . 'tests' . DS;
        $result = $command->findPath($paths, $io);

        $this->assertNull($result, 'no return');
        $this->assertSame(TMP . 'plugin_task' . DS, $command->path);
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
        $comparisonFiles = $this->getFiles($comparisonRoot);

        $bakedRoot = App::path('plugins')[0] . $pluginName . DS;
        $bakedFiles = $this->getFiles($bakedRoot);

        $this->assertCount(
            count($comparisonFiles),
            $bakedFiles,
            'A different number of files were created than expected'
        );

        foreach ($comparisonFiles as $key => $file) {
            $result = file_get_contents($file);
            $this->assertSameAsFile($bakedFiles[$key], $result);
        }
    }

    /**
     * Get recursive files list for given path.
     *
     * @param string $path
     * @return string[]
     */
    protected function getFiles(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $fs = new Filesystem();

        $iterator = $fs->findRecursive(
            $path,
            function (SplFileInfo $fileInfo) {
                return $fileInfo->isFile();
            }
        );

        $files = array_keys(iterator_to_array($iterator));
        sort($files);

        return $files;
    }
}
