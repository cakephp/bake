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
namespace Bake\Test\TestCase\Shell\Task;

use Bake\Shell\Task\BakeTemplateTask;
use Bake\Test\App\Model\Table\BakeArticlesTable;
use Bake\Test\TestCase\TestCase;
use Cake\Console\Shell;
use Cake\Core\Plugin;
use Cake\ORM\TableRegistry;

/**
 * ControllerTaskTest class
 */
class ControllerTaskTest extends TestCase
{
    /**
     * fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Bake.BakeArticles',
        'plugin.Bake.BakeArticlesBakeTags',
        'plugin.Bake.BakeComments',
        'plugin.Bake.BakeTags',
    ];

    /**
     * @var \Bake\Shell\Task\ModelTask|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $Task;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Controller' . DS;
        $io = $this->getMockBuilder('Cake\Console\ConsoleIo')
            ->disableOriginalConstructor()
            ->getMock();
        $this->Task = $this->getMockBuilder('Bake\Shell\Task\ControllerTask')
            ->setMethods(['in', 'out', 'err', 'hr', 'createFile', '_stop'])
            ->setConstructorArgs([$io])
            ->getMock();

        $this->Task->name = 'Controller';
        $this->Task->connection = 'test';
        $this->Task->BakeTemplate = new BakeTemplateTask($io);

        $this->Task->Model = $this->getMockBuilder('Bake\Shell\Task\ModelTask')
            ->setMethods(['in', 'out', 'err', 'createFile', '_stop'])
            ->setConstructorArgs([$io])
            ->setMethods(['bake'])
            ->getMock();

        $this->Task->Test = $this->getMockBuilder('Bake\Shell\Task\TestTask')
            ->setConstructorArgs([$io])
            ->setMethods(['bake'])
            ->getMock();

        TableRegistry::getTableLocator()->get('BakeArticles', [
            'className' => BakeArticlesTable::class,
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Task);
        TableRegistry::getTableLocator()->clear();

        parent::tearDown();

        $this->removePlugins(['ControllerTest', 'Company/Pastry']);
    }

    /**
     * test main listing available models.
     *
     * @return void
     */
    public function testMainListAvailable()
    {
        $this->exec('bake controller');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('- BakeArticles');
        $this->assertOutputContains('- BakeArticlesBakeTags');
        $this->assertOutputContains('- BakeComments');
        $this->assertOutputContains('- BakeTags');
    }

    /**
     * test component generation
     *
     * @return void
     */
    public function testGetComponents()
    {
        $result = $this->Task->getComponents();
        $this->assertSame([], $result);

        $this->Task->params['components'] = '  , Security, ,  Csrf';
        $result = $this->Task->getComponents();
        $this->assertSame(['Security', 'Csrf'], $result);
    }

    /**
     * test helper generation
     *
     * @return void
     */
    public function testGetHelpers()
    {
        $result = $this->Task->getHelpers();
        $this->assertSame([], $result);

        $this->Task->params['helpers'] = '  , Session , ,  Number';
        $result = $this->Task->getHelpers();
        $this->assertSame(['Session', 'Number'], $result);
    }

    /**
     * test bake with various component name variants
     *
     * @return void
     */
    public function testBakeComponents()
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec(
            'bake controller --connection test --no-test --no-actions ' .
            '--components "Csrf, Auth, Company/TestBakeThree.Something, TestBake.Other, Apple, NonExistent" ' .
            'BakeArticles'
        );

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test the bake method
     *
     * @return void
     */
    public function testBakeActionsOption()
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec(
            'bake controller --connection test --no-test ' .
            '--helpers Html,Time --components Csrf,Auth ' .
            '--actions login,logout BakeArticles'
        );

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test the bake method
     *
     * @return void
     */
    public function testBakeNoActions()
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec(
            'bake controller --connection test --no-test ' .
            '--helpers Html,Time --components Csrf,Auth --no-actions BakeArticles'
        );

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test bake with actions.
     *
     * @return void
     */
    public function testBakeActions()
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec(
            'bake controller --connection test --no-test ' .
            '--helpers Html,Time --components "Csrf, Auth" BakeArticles'
        );

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test bake actions prefixed.
     *
     * @return void
     */
    public function testBakePrefixed()
    {
        $this->generatedFile = APP . 'Controller/Admin/BakeArticlesController.php';
        $this->exec('bake controller --connection test --no-test --prefix admin BakeArticles');

        $this->assertFileContains('namespace App\Controller\Admin;', $this->generatedFile);
        $this->assertFileContains('use App\Controller\AppController;', $this->generatedFile);
        $this->assertFileContains('class BakeArticlesController extends', $this->generatedFile);
    }

    /**
     * test bake actions with nested prefixes.
     *
     * @return void
     */
    public function testBakePrefixNested()
    {
        $this->generatedFile = APP . 'Controller/Admin/Management/BakeArticlesController.php';
        $this->exec('bake controller --connection test --no-test --prefix admin/management BakeArticles');

        $this->assertFileContains('namespace App\Controller\Admin\Management;', $this->generatedFile);
        $this->assertFileContains('use App\Controller\AppController;', $this->generatedFile);
        $this->assertFileContains('class BakeArticlesController extends', $this->generatedFile);
    }

    /**
     * test bake() with a -plugin param
     *
     * @return void
     */
    public function testBakeWithPlugin()
    {
        $this->_loadTestPlugin('BakeTest');
        $path = Plugin::path('BakeTest');

        $this->generatedFile = $path . 'src/Controller/BakeArticlesController.php';
        $this->exec('bake controller --connection test --no-test BakeTest.BakeArticles');

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test that bakeActions is creating the correct controller Code. (Using sessions)
     *
     * @return void
     */
    public function testBakeActionsContent()
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec('bake controller --connection test --no-test BakeArticles');

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking a test
     *
     * @return void
     */
    public function testBakeTest()
    {
        $this->generatedFiles = [
            APP . 'Controller/BakeArticlesController.php',
            ROOT . 'tests/TestCase/Controller/BakeArticlesControllerTest.php',
        ];
        $this->exec('bake controller --connection test BakeArticles');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains(
            'class BakeArticlesControllerTest extends TestCase',
            $this->generatedFiles[1]
        );
        $this->assertFileContains(
            'use IntegrationTestTrait',
            $this->generatedFiles[1]
        );
    }

    /**
     * test baking a test
     *
     * @return void
     */
    public function testBakeTestDisabled()
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec('bake controller --connection test --no-test BakeArticles');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileNotExists(ROOT . 'tests/TestCase/Controller/BakeArticlesControllerTest.php');
        $this->assertFileExists($this->generatedFile);
    }

    /**
     * Test execute no args.
     *
     * @return void
     */
    public function testMainNoArgs()
    {
        $this->exec('bake controller');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('Possible controllers based on your current database');
        $this->assertOutputContains('- BakeArticles');
    }

    /**
     * test that execute runs all when the first arg == all
     *
     * @return void
     */
    public function testMainIntoAll()
    {
        if ($this->Task->listAll()[0] != 'bake_articles') {
            $this->markTestSkipped('Additional tables detected.');
        }

        $this->Task->connection = 'test';
        $this->Task->params = ['helpers' => 'Time,Text'];

        $this->Task->Test->expects($this->atLeastOnce())
            ->method('bake');

        $filename = $this->_normalizePath(APP . 'Controller/BakeArticlesController.php');
        $this->Task->expects($this->at(1))
            ->method('createFile')
            ->with($filename, $this->logicalAnd(
                $this->stringContains('class BakeArticlesController'),
                $this->stringContains("\$helpers = ['Time', 'Text']")
            ))
            ->will($this->returnValue(true));

        $this->Task->all();
    }

    /**
     * data provider for testMainWithControllerNameVariations
     *
     * @return void
     */
    public static function nameVariations()
    {
        return [
            ['BakeArticles'], ['bake_articles'],
        ];
    }

    /**
     * test that both plural and singular forms work for controller baking.
     *
     * @dataProvider nameVariations
     * @return void
     */
    public function testMainWithControllerNameVariations($name)
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec("bake controller --connection test --no-test {$name}");
        $this->assertExitCode(Shell::CODE_SUCCESS);

        $this->assertFileExists($this->generatedFile);
        $this->assertFileContains('BakeArticlesController extends AppController', $this->generatedFile);
    }

    /**
     * test main with plugin.name
     *
     * @return void
     */
    public function testMainWithPluginDot()
    {
        $this->_loadTestPlugin('Company/Pastry');
        $path = Plugin::path('Company/Pastry');

        $this->generatedFile = $path . 'src/Controller/BakeArticlesController.php';

        $this->exec('bake controller --connection test --no-test Company/Pastry.BakeArticles');
        $this->assertExitCode(Shell::CODE_SUCCESS);

        $this->assertFileExists($this->generatedFile);
        $this->assertFileContains('namespace Company\Pastry\Controller;', $this->generatedFile);
        $this->assertFileContains('BakeArticlesController extends AppController', $this->generatedFile);
    }

    /**
     * test main with plugin.name
     *
     * @return void
     */
    public function testMainWithPluginOption()
    {
        $this->_loadTestPlugin('Company/Pastry');
        $path = Plugin::path('Company/Pastry');

        $this->generatedFile = $path . 'src/Controller/BakeArticlesController.php';

        $this->exec('bake controller --connection test --no-test --plugin Company/Pastry bake_articles');
        $this->assertExitCode(Shell::CODE_SUCCESS);

        $this->assertFileExists($this->generatedFile);
        $this->assertFileContains('namespace Company\Pastry\Controller;', $this->generatedFile);
        $this->assertFileContains('BakeArticlesController extends AppController', $this->generatedFile);
    }
}
