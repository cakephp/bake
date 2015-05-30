<?php
/**
 * CakePHP :  Rapid Development Framework (http://cakephp.org)
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
use Bake\Shell\Task\TestTask;
use Bake\Test\App\Controller\PostsController;
use Bake\Test\App\Model\Table\ArticlesTable;
use Bake\Test\App\Model\Table\CategoryThreadsTable;
use Bake\Test\TestCase\TestCase;
use Cake\Controller\Controller;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * TestTaskTest class
 *
 */
class TestTaskTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var string
     */
    public $fixtures = [
        'core.articles',
        'core.tags',
        'core.articles_tags',
        'core.authors',
        'core.comments',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Test' . DS;
        $this->io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Task = $this->getMock(
            'Bake\Shell\Task\TestTask',
            ['in', 'err', 'createFile', '_stop', 'isLoadableClass'],
            [$this->io]
        );
        $this->Task->name = 'Test';
        $this->Task->BakeTemplate = new BakeTemplateTask($this->io);
        $this->Task->BakeTemplate->interactive = false;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->Task);
    }

    /**
     * Test that with no args execute() outputs the types you can generate
     * tests for.
     *
     * @return void
     */
    public function testExecuteNoArgsPrintsTypeOptions()
    {
        $this->Task = $this->getMockBuilder('Bake\Shell\Task\TestTask')
            ->disableOriginalConstructor()
            ->setMethods(['outputTypeChoices'])
            ->getMock();

        $this->Task->expects($this->once())
            ->method('outputTypeChoices');

        $this->Task->main();
    }

    /**
     * Test outputTypeChoices method
     *
     * @return void
     */
    public function testOutputTypeChoices()
    {
        $this->io->expects($this->at(0))
            ->method('out')
            ->with($this->stringContains('You must provide'));
        $this->io->expects($this->at(1))
            ->method('out')
            ->with($this->stringContains('1. Entity'));
        $this->io->expects($this->at(2))
            ->method('out')
            ->with($this->stringContains('2. Table'));
        $this->io->expects($this->at(3))
            ->method('out')
            ->with($this->stringContains('3. Controller'));
        $this->Task->outputTypeChoices();
    }

    /**
     * Test that with no args execute() outputs the types you can generate
     * tests for.
     *
     * @return void
     */
    public function testExecuteOneArgPrintsClassOptions()
    {
        $this->Task = $this->getMockBuilder('Bake\Shell\Task\TestTask')
            ->disableOriginalConstructor()
            ->setMethods(['outputClassChoices'])
            ->getMock();

        $this->Task->expects($this->once())
            ->method('outputClassChoices');

        $this->Task->main('Entity');
    }

    /**
     * test execute with type and class name defined
     *
     * @return void
     */
    public function testExecuteWithTwoArgs()
    {
        $this->Task->expects($this->once())->method('createFile')
            ->with(
                $this->stringContains('TestCase' . DS . 'Model' . DS . 'Table' . DS . 'TestTaskTagTableTest.php'),
                $this->stringContains('class TestTaskTagTableTest extends TestCase')
            );
        $this->Task->main('Table', 'TestTaskTag');
    }

    /**
     * test execute with type and class name defined
     *
     * @return void
     */
    public function testExecuteWithAll()
    {
        $this->Task->expects($this->exactly(2))->method('createFile')
            ->withConsecutive(
                [
                    $this->stringContains('TestCase' . DS . 'Model' . DS . 'Table' . DS . 'ArticlesTableTest.php'),
                    $this->stringContains('class ArticlesTableTest extends TestCase')
                ],
                [
                    $this->stringContains('TestCase' . DS . 'Model' . DS . 'Table' . DS . 'CategoryThreadsTableTest.php'),
                    $this->stringContains('class CategoryThreadsTableTest extends TestCase')
                ]
            );
        $this->Task->params['all'] = true;
        $this->Task->main('Table');
    }

    /**
     * Test generating class options for table.
     *
     * @return void
     */
    public function testOutputClassOptionsForTable()
    {
        $expected = [
            'ArticlesTable',
            'CategoryThreadsTable'
        ];

        $choices = $this->Task->outputClassChoices('Table');
        $this->assertSame($expected, $choices);
    }

    /**
     * Test generating class options for table.
     *
     * @return void
     */
    public function testOutputClassOptionsForTablePlugin()
    {
        Plugin::load('BakeTest');
        $this->Task->plugin = 'BakeTest';

        $expected = [
            'AuthorsTable',
            'BakeArticlesTable',
            'BakeTestCommentsTable',
            'CommentsTable'
        ];

        $choices = $this->Task->outputClassChoices('Table');
        $this->assertSame($expected, $choices);
    }

    /**
     * Test that method introspection pulls all relevant non parent class
     * methods into the test case.
     *
     * @return void
     */
    public function testMethodIntrospection()
    {
        $result = $this->Task->getTestableMethods('Bake\Test\App\Model\Table\ArticlesTable');
        $expected = ['initialize', 'findpublished', 'dosomething', 'dosomethingelse'];
        $this->assertEquals($expected, array_map('strtolower', $result));
    }

    /**
     * test that the generation of fixtures works correctly.
     *
     * @return void
     */
    public function testFixtureArrayGenerationFromModel()
    {
        $subject = new ArticlesTable();
        $result = $this->Task->generateFixtureList($subject);
        $expected = [
            'app.articles',
            'app.authors',
            'app.tags',
            'app.articles_tags'
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * test that the generation of fixtures works correctly.
     *
     * @return void
     */
    public function testFixtureArrayGenerationIgnoreSelfAssociation()
    {
        TableRegistry::clear();
        $subject = new CategoryThreadsTable();
        $result = $this->Task->generateFixtureList($subject);
        $expected = [
            'app.category_threads',
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * test that the generation of fixtures works correctly.
     *
     * @return void
     */
    public function testFixtureArrayGenerationFromController()
    {
        $subject = new PostsController(new Request(), new Response());
        $result = $this->Task->generateFixtureList($subject);
        $expected = [
            'app.posts',
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Dataprovider for class name generation.
     *
     * @return array
     */
    public static function realClassProvider()
    {
        return [
            ['Entity', 'Article', 'App\Model\Entity\Article'],
            ['entity', 'ArticleEntity', 'App\Model\Entity\ArticleEntity'],
            ['Table', 'Posts', 'App\Model\Table\PostsTable'],
            ['table', 'PostsTable', 'App\Model\Table\PostsTable'],
            ['Controller', 'Posts', 'App\Controller\PostsController'],
            ['controller', 'PostsController', 'App\Controller\PostsController'],
            ['Behavior', 'Timestamp', 'App\Model\Behavior\TimestampBehavior'],
            ['behavior', 'TimestampBehavior', 'App\Model\Behavior\TimestampBehavior'],
            ['Helper', 'Form', 'App\View\Helper\FormHelper'],
            ['helper', 'FormHelper', 'App\View\Helper\FormHelper'],
            ['Component', 'Auth', 'App\Controller\Component\AuthComponent'],
            ['component', 'AuthComponent', 'App\Controller\Component\AuthComponent'],
            ['Shell', 'Example', 'App\Shell\ExampleShell'],
            ['shell', 'Example', 'App\Shell\ExampleShell'],
            ['Cell', 'Example', 'App\View\Cell\ExampleCell'],
            ['cell', 'Example', 'App\View\Cell\ExampleCell'],
        ];
    }

    /**
     * test that resolving class names works
     *
     * @dataProvider realClassProvider
     * @return void
     */
    public function testGetRealClassname($type, $name, $expected)
    {
        $result = $this->Task->getRealClassname($type, $name);
        $this->assertEquals($expected, $result);
    }

    /**
     * test resolving class names with plugins
     *
     * @return void
     */
    public function testGetRealClassnamePlugin()
    {
        $this->_loadTestPlugin('TestBake');
        $this->Task->plugin = 'TestBake';
        $result = $this->Task->getRealClassname('Helper', 'Asset');
        $expected = 'TestBake\View\Helper\AssetHelper';
        $this->assertEquals($expected, $result);
    }

    /**
     * Test baking a test for a concrete model with fixtures arg
     *
     * @return void
     */
    public function testBakeFixturesParam()
    {
        $this->Task->expects($this->once())
            ->method('createFile')
            ->will($this->returnValue(true));

        $this->Task->params['fixtures'] = 'app.posts, app.comments , app.users ,';
        $result = $this->Task->bake('Table', 'Articles');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Test baking a test for a cell.
     *
     * @return void
     */
    public function testBakeCellTest()
    {
        $this->Task->expects($this->once())
            ->method('createFile')
            ->will($this->returnValue(true));

        $result = $this->Task->bake('Cell', 'Articles');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Test baking a test for a concrete model.
     *
     * @return void
     */
    public function testBakeModelTest()
    {
        $this->Task->expects($this->once())
            ->method('createFile')
            ->will($this->returnValue(true));

        $result = $this->Task->bake('Table', 'Articles');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking controller test files
     *
     * @return void
     */
    public function testBakeControllerTest()
    {
        Configure::write('App.namespace', 'Bake\Test\App');

        $this->Task->expects($this->once())
            ->method('createFile')
            ->will($this->returnValue(true));

        $result = $this->Task->bake('Controller', 'PostsController');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking controller test files
     *
     * @return void
     */
    public function testBakePrefixControllerTest()
    {
        Configure::write('App.namespace', 'Bake\Test\App');

        $this->Task->expects($this->once())
            ->method('createFile')
            ->with($this->stringContains('Controller' . DS . 'Admin' . DS . 'PostsControllerTest.php'))
            ->will($this->returnValue(true));

        $result = $this->Task->bake('controller', 'Admin\Posts');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking component test files,
     *
     * @return void
     */
    public function testBakeComponentTest()
    {
        Configure::write('App.namespace', 'Bake\Test\App');

        $this->Task->expects($this->once())
            ->method('createFile')
            ->will($this->returnValue(true));

        $result = $this->Task->bake('Component', 'Apple');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking behavior test files,
     *
     * @return void
     */
    public function testBakeBehaviorTest()
    {
        $this->Task->expects($this->once())
            ->method('createFile')
            ->will($this->returnValue(true));

        $result = $this->Task->bake('Behavior', 'Example');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking helper test files,
     *
     * @return void
     */
    public function testBakeHelperTest()
    {
        $this->Task->expects($this->once())
            ->method('createFile')
            ->will($this->returnValue(true));

        $result = $this->Task->bake('Helper', 'Example');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Test baking a test for a concrete model.
     *
     * @return void
     */
    public function testBakeShellTest()
    {
        $this->Task->expects($this->once())
            ->method('createFile')
            ->will($this->returnValue(true));

        $result = $this->Task->bake('Shell', 'Articles');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test Constructor generation ensure that constructClasses is called for controllers
     *
     * @return void
     */
    public function testGenerateConstructor()
    {
        $result = $this->Task->generateConstructor('controller', 'PostsController');
        $expected = ['', '', ''];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateConstructor('table', 'App\Model\\Table\PostsTable');
        $expected = [
            "\$config = TableRegistry::exists('Posts') ? [] : ['className' => 'App\Model\\Table\PostsTable'];",
            "TableRegistry::get('Posts', \$config);",
            ''
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateConstructor('helper', 'FormHelper');
        $expected = ["\$view = new View();", "new FormHelper(\$view);", ''];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateConstructor('entity', 'TestBake\Model\Entity\Article');
        $expected = ["", "new Article();", ''];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateConstructor('shell_helper', 'TestBake\Shell\Helper\ExampleHelper');
        $expected = [
            "\$this->stub = new ConsoleOutput();\n        \$this->io = new ConsoleIo(\$this->stub);",
            "new ExampleHelper(\$this->io);",
            ''
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateConstructor('form', 'TestBake\Form\ExampleForm');
        $expected = [
            '',
            "new ExampleForm();",
            ''
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test generateUses()
     *
     * @return void
     */
    public function testGenerateUses()
    {
        $result = $this->Task->generateUses('table', 'App\Model\Table\PostsTable');
        $expected = [
            'Cake\ORM\TableRegistry',
            'App\Model\Table\PostsTable',
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateUses('controller', 'App\Controller\PostsController');
        $expected = [
            'App\Controller\PostsController',
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateUses('helper', 'App\View\Helper\FormHelper');
        $expected = [
            'Cake\View\View',
            'App\View\Helper\FormHelper',
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateUses('component', 'App\Controller\Component\AuthComponent');
        $expected = [
            'Cake\Controller\ComponentRegistry',
            'App\Controller\Component\AuthComponent',
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateUses('shell_helper', 'App\Shell\Helper\ExampleHelper');
        $expected = [
            'Cake\TestSuite\Stub\ConsoleOutput',
            'Cake\Console\ConsoleIo',
            'App\Shell\Helper\ExampleHelper',
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that mock class generation works for the appropriate classes
     *
     * @return void
     */
    public function testMockClassGeneration()
    {
        $result = $this->Task->hasMockClass('controller');
        $this->assertTrue($result);
    }

    /**
     * test bake() with a -plugin param
     *
     * @return void
     */
    public function testBakeWithPlugin()
    {
        $this->Task->plugin = 'TestTest';

        Plugin::load('TestTest', ['path' => APP . 'Plugin' . DS . 'TestTest' . DS]);
        $path = APP . 'Plugin/TestTest/tests/TestCase/View/Helper/FormHelperTest.php';
        $path = str_replace('/', DS, $path);
        $this->Task->expects($this->once())->method('createFile')
            ->with($path, $this->anything());

        $this->Task->bake('Helper', 'Form');
    }

    /**
     * Provider for test case file names.
     *
     * @return array
     */
    public static function caseFileNameProvider()
    {
        return [
            ['Table', 'App\Model\Table\PostsTable', 'TestCase/Model/Table/PostsTableTest.php'],
            ['Entity', 'App\Model\Entity\Article', 'TestCase/Model/Entity/ArticleTest.php'],
            ['Helper', 'App\View\Helper\FormHelper', 'TestCase/View/Helper/FormHelperTest.php'],
            ['Controller', 'App\Controller\PostsController', 'TestCase/Controller/PostsControllerTest.php'],
            ['Controller', 'App\Controller\Admin\PostsController', 'TestCase/Controller/Admin/PostsControllerTest.php'],
            ['Behavior', 'App\Model\Behavior\TreeBehavior', 'TestCase/Model/Behavior/TreeBehaviorTest.php'],
            [
                'Component',
                'App\Controller\Component\AuthComponent',
                'TestCase/Controller/Component/AuthComponentTest.php'
            ],
            ['entity', 'App\Model\Entity\Article', 'TestCase/Model/Entity/ArticleTest.php'],
            ['table', 'App\Model\Table\PostsTable', 'TestCase/Model/Table/PostsTableTest.php'],
            ['helper', 'App\View\Helper\FormHelper', 'TestCase/View/Helper/FormHelperTest.php'],
            ['controller', 'App\Controller\PostsController', 'TestCase/Controller/PostsControllerTest.php'],
            ['behavior', 'App\Model\Behavior\TreeBehavior', 'TestCase/Model/Behavior/TreeBehaviorTest.php'],
            [
                'component',
                'App\Controller\Component\AuthComponent',
                'TestCase/Controller/Component/AuthComponentTest.php'
            ],
            ['Shell', 'App\Shell\ExampleShell', 'TestCase/Shell/ExampleShellTest.php'],
            ['shell', 'App\Shell\ExampleShell', 'TestCase/Shell/ExampleShellTest.php'],
        ];
    }

    /**
     * Test filename generation for each type + plugins
     *
     * @dataProvider caseFileNameProvider
     * @return void
     */
    public function testTestCaseFileName($type, $class, $expected)
    {
        $result = $this->Task->testCaseFileName($type, $class);
        $this->assertPathEquals(ROOT . DS . 'tests' . DS . $expected, $result);
    }

    /**
     * Test filename generation for plugins.
     *
     * @return void
     */
    public function testTestCaseFileNamePlugin()
    {
        $this->Task->path = DS . 'my/path/tests/';

        Plugin::load('TestTest', ['path' => APP . 'Plugin' . DS . 'TestTest' . DS]);
        $this->Task->plugin = 'TestTest';
        $class = 'TestBake\Model\Entity\Post';
        $result = $this->Task->testCaseFileName('entity', $class);

        $expected = APP . 'Plugin/TestTest/tests/TestCase/Model/Entity/PostTest.php';
        $this->assertPathEquals($expected, $result);
    }

    /**
     * Data provider for mapType() tests.
     *
     * @return array
     */
    public static function mapTypeProvider()
    {
        return [
            ['controller', 'Controller'],
            ['Controller', 'Controller'],
            ['component', 'Controller\Component'],
            ['Component', 'Controller\Component'],
            ['table', 'Model\Table'],
            ['Table', 'Model\Table'],
            ['entity', 'Model\Entity'],
            ['Entity', 'Model\Entity'],
            ['behavior', 'Model\Behavior'],
            ['Behavior', 'Model\Behavior'],
            ['helper', 'View\Helper'],
            ['Helper', 'View\Helper'],
            ['Helper', 'View\Helper'],
        ];
    }

    /**
     * Test that mapType returns the correct package names.
     *
     * @dataProvider mapTypeProvider
     * @return void
     */
    public function testMapType($original, $expected)
    {
        $this->assertEquals($expected, $this->Task->mapType($original));
    }
}
