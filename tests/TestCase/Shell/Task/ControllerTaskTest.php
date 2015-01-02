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

use Bake\Shell\Task\TemplateTask;
use Bake\Test\TestCase\TestCase;
use Cake\Core\Plugin;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;

/**
 * ControllerTaskTest class
 *
 */
class ControllerTaskTest extends TestCase
{
    /**
     * fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.bake.bake_articles',
        'plugin.bake.bake_articles_bake_tags',
        'plugin.bake.bake_comments',
        'plugin.bake.bake_tags'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Controller' . DS;
        $io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);
        $this->Task = $this->getMock(
            'Bake\Shell\Task\ControllerTask',
            ['in', 'out', 'err', 'hr', 'createFile', '_stop'],
            [$io]
        );
        $this->Task->name = 'Controller';
        $this->Task->connection = 'test';

        $this->Task->Template = new TemplateTask($io);

        $this->Task->Model = $this->getMock(
            'Bake\Shell\Task\ModelTask',
            ['in', 'out', 'err', 'createFile', '_stop'],
            [$io]
        );
        $this->Task->Test = $this->getMock(
            'Bake\Shell\Task\TestTask',
            [],
            [$io]
        );

        TableRegistry::get('BakeArticles', [
            'className' => __NAMESPACE__ . '\BakeArticlesTable'
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
        TableRegistry::clear();
        parent::tearDown();
        Plugin::unload('ControllerTest');
    }

    /**
     * test ListAll
     *
     * @return void
     */
    public function testListAll()
    {
        $result = $this->Task->listAll();
        $this->assertContains('bake_articles', $result);
        $this->assertContains('bake_articles_bake_tags', $result);
        $this->assertContains('bake_comments', $result);
        $this->assertContains('bake_tags', $result);
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
        $this->Task->expects($this->any())
            ->method('createFile')
            ->will($this->returnValue(true));

        $this->Task->params['no-actions'] = true;
        $this->Task->params['components'] = 'Csrf, Auth, Company/TestBakeThree.Something,' .
           ' TestBake.Other, Apple, NonExistent';

        $result = $this->Task->bake('BakeArticles');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test the bake method
     *
     * @return void
     */
    public function testBakeNoActions()
    {
        $this->Task->expects($this->any())
            ->method('createFile')
            ->will($this->returnValue(true));

        $this->Task->params['no-actions'] = true;
        $this->Task->params['helpers'] = 'Html,Time';
        $this->Task->params['components'] = 'Csrf, Auth';

        $result = $this->Task->bake('BakeArticles');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test bake with actions.
     *
     * @return void
     */
    public function testBakeActions()
    {
        $this->Task->params['helpers'] = 'Html,Time';
        $this->Task->params['components'] = 'Csrf, Auth';

        $filename = APP . 'Controller/BakeArticlesController.php';
        $this->Task->expects($this->at(1))
            ->method('createFile')
            ->with(
                $this->_normalizePath($filename),
                $this->stringContains('class BakeArticlesController')
            );
        $result = $this->Task->bake('BakeArticles');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test bake actions prefixed.
     *
     * @return void
     */
    public function testBakePrefixed()
    {
        $this->Task->params['prefix'] = 'admin';

        $filename = $this->_normalizePath(APP . 'Controller/Admin/BakeArticlesController.php');
        $this->Task->expects($this->at(1))
            ->method('createFile')
            ->with($filename, $this->anything());

        $this->Task->Test->expects($this->at(0))
            ->method('bake')
            ->with('Controller', 'Admin\BakeArticles');
        $result = $this->Task->bake('BakeArticles');

        $this->assertTextContains('namespace App\Controller\Admin;', $result);
        $this->assertTextContains('use App\Controller\AppController;', $result);
    }

    /**
     * test bake() with a -plugin param
     *
     * @return void
     */
    public function testBakeWithPlugin()
    {
        $this->Task->plugin = 'ControllerTest';

        Plugin::load('ControllerTest', ['path' => APP . 'Plugin/ControllerTest/']);
        $path = APP . 'Plugin/ControllerTest/src/Controller/BakeArticlesController.php';

        $this->Task->expects($this->at(1))
            ->method('createFile')
            ->with($this->_normalizePath($path))
            ->will($this->returnValue(true));

        $result = $this->Task->bake('BakeArticles');

        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     *
     * test that bakeActions is creating the correct controller Code. (Using sessions)
     *
     * @return void
     */
    public function testBakeActionsContent()
    {
        $result = $this->Task->bake('BakeArticles');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking a test
     *
     * @return void
     */
    public function testBakeTest()
    {
        $this->Task->plugin = 'ControllerTest';
        $this->Task->connection = 'test';

        $this->Task->Test->expects($this->once())
            ->method('bake')
            ->with('Controller', 'BakeArticles');
        $this->Task->bakeTest('BakeArticles');

        $this->assertEquals($this->Task->plugin, $this->Task->Test->plugin);
        $this->assertEquals($this->Task->connection, $this->Task->Test->connection);
    }

    /**
     * test baking a test
     *
     * @return void
     */
    public function testBakeTestDisabled()
    {
        $this->Task->plugin = 'ControllerTest';
        $this->Task->connection = 'test';
        $this->Task->params['no-test'] = true;

        $this->Task->Test->expects($this->never())
            ->method('bake');
        $this->Task->bakeTest('BakeArticles');
    }

    /**
     * Test execute no args.
     *
     * @return void
     */
    public function testMainNoArgs()
    {
        $this->Task->expects($this->never())
            ->method('createFile');

        $this->Task->expects($this->at(0))
            ->method('out')
            ->with($this->stringContains('Possible controllers based on your current database'));

        $this->Task->main();
    }

    /**
     * test that execute runs all when the first arg == all
     *
     * @return void
     */
    public function testMainIntoAll()
    {
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
            ['BakeArticles'], ['bake_articles']
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
        $this->Task->connection = 'test';

        $filename = $this->_normalizePath(APP . 'Controller/BakeArticlesController.php');
        $this->Task->expects($this->once())
            ->method('createFile')
            ->with($filename, $this->stringContains('public function index()'));
        $this->Task->main($name);
    }

    /**
     * test main with plugin.name
     *
     * @return void
     */
    public function testMainWithPluginDot()
    {
        $this->Task->connection = 'test';

        Plugin::load('ControllerTest', ['path' => APP . 'Plugin/ControllerTest/']);
        $path = APP . 'Plugin/ControllerTest/src/Controller/BakeArticlesController.php';

        $this->Task->expects($this->at(1))
            ->method('createFile')
            ->with(
                $this->_normalizePath($path),
                $this->stringContains('BakeArticlesController extends AppController')
            )->will($this->returnValue(true));

        $this->Task->main('ControllerTest.BakeArticles');
    }
}
