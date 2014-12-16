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
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\TestSuite\TestCase;

/**
 * PluginTaskPlugin class
 */
class PluginTaskTest extends TestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

		$this->Task = $this->getMock('Bake\Shell\Task\PluginTask',
			array('in', 'err', 'createFile', '_stop', 'clear', 'callProcess'),
			array($this->io)
		);

		$this->Task->Project = new ProjectTask($this->io);

		$this->Task->Template = new TemplateTask($this->io);
		$this->Task->Template->interactive = false;

		$this->Task->path = TMP . 'tests' . DS;
		$this->Task->bootstrap = TMP . 'tests' . DS . 'bootstrap.php';

		if (!is_dir($this->Task->path)) {
			mkdir($this->Task->path);
		}
		touch($this->Task->bootstrap);

		$this->_path = App::path('Plugin');
	}

/**
 * tearDown()
 *
 * @return void
 */
	public function tearDown() {
		if (file_exists($this->Task->bootstrap)) {
			unlink($this->Task->bootstrap);
		}
		parent::tearDown();
	}

/**
 * test bake()
 *
 * @return void
 */
	public function testBakeFoldersAndFiles() {
		$this->Task->expects($this->at(0))->method('in')->will($this->returnValue('y'));

		$path = $this->Task->path . 'BakeTestBake';

		$files = [
			'README.md',
			'composer.json',
			'config/routes.php',
			'phpunit.xml.dist',
			'src/Controller/AppController.php',
			'tests/bootstrap.php',
			'webroot/empty'
		];

		$i = 1;
		foreach($files as $file) {
				$this->Task->expects($this->at($i++))
					->method('createFile')
					->with($path . DS . $file);
		}

		$this->Task->bake('BakeTestBake');
	}

/**
 * test execute with no args, flowing into interactive,
 *
 * @return void
 */
	public function testExecuteWithNoArgs() {
		$path = $this->Task->path . 'TestBake';

		$this->Task->expects($this->at(0))
			->method('err')
			->with($this->stringContains('You must'));

		$this->Task->expects($this->never())
			->method('createFile');

		$this->Task->main();

		$Folder = new Folder($path);
		$Folder->delete();
	}

/**
 * Test Execute
 *
 * @return void
 */
	public function testExecuteWithOneArg() {
		$this->Task->expects($this->at(0))->method('in')
			->will($this->returnValue('y'));

		$path = $this->Task->path . 'BakeTestBake';
		$files = [
			'README.md',
			'composer.json',
			'config/routes.php',
			'phpunit.xml.dist',
			'src/Controller/AppController.php',
			'tests/bootstrap.php',
			'webroot/empty'
		];

		$i = 1;
		foreach($files as $file) {
				$this->Task->expects($this->at($i++))
					->method('createFile')
					->with($path . DS . $file);
		}

		$this->Task->main('BakeTestBake');
	}

/**
 * Test that baking a plugin for a project that contains a composer.json, the later
 * will be updated
 *
 * @return void
 */
	public function testExecuteUpdateComposer() {
		$this->Task->expects($this->at(0))->method('in')
			->will($this->returnValue('y'));

		$this->Task->Project = $this->getMock('ComposerProject', ['findComposer']);
		$this->Task->Project->expects($this->at(0))
			->method('findComposer')
			->will($this->returnValue('composer.phar'));

		$path = $this->Task->path . 'BakeTestBake';
		if (!is_dir($path)) {
			mkdir($path);
		}
		$file = $path . DS . 'composer.json';
		file_put_contents($file, '{}');

		$config = [
			'name' => 'your-name-here/BakeTestBake',
			'description' => 'BakeTestBake plugin for CakePHP',
			'type' => 'cakephp-plugin',
			'require' => [
				'php' => '>=5.4',
				'cakephp/plugin-installer' => '*',
				'cakephp/cakephp' => '3.0.x-dev'
			],
			'require-dev' => [
				'phpunit/phpunit' => '*'
			],
			'autoload' => [
				'psr-4' => [
					'BakeTestBake\\' => 'src',
				],
			],
			'autoload-dev' => [
				'psr-4' => [
					'BakeTestBake\\Test\\' => 'tests',
					'Cake\\Test\\' => './vendor/cakephp/cakephp/tests',
				],
			],
		];
		$config = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

		$this->Task->expects($this->at(2))
			->method('createFile')
			->with($file, $config);

		$this->Task->expects($this->once())
			->method('callProcess')
			->with('php ' . escapeshellarg('composer.phar') . ' dump-autoload');

		$this->Task->main('BakeTestBake');

		$Folder = new Folder($this->Task->path . 'BakeTestBake');
		$Folder->delete();

		$File = new File($file);
		$File->delete();
	}

/**
 * Test that findPath ignores paths that don't exist.
 *
 * @return void
 */
	public function testFindPathNonExistant() {
		$paths = App::path('Plugin');
		$last = count($paths);

		array_unshift($paths, '/fake/path');
		$paths[] = '/fake/path2';

		$this->Task = $this->getMock('Bake\Shell\Task\PluginTask',
			array('in', 'out', 'err', 'createFile', '_stop'),
			array($this->io)
		);
		$this->Task->path = TMP . 'tests' . DS;

		// Make sure the added path is filtered out.
		$this->Task->expects($this->exactly($last))
			->method('out');

		$this->Task->expects($this->once())
			->method('in')
			->will($this->returnValue($last));

		$this->Task->findPath($paths);
	}

}
