<?php
declare(strict_types=1);

/**
 * CakePHP :  Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP Project
 * @since         0.1.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Command;

use Bake\Command\TestCommand;
use Bake\Test\App\Controller\PostsController;
use Bake\Test\App\Model\Table\ArticlesTable;
use Bake\Test\App\Model\Table\CategoryThreadsTable;
use Bake\Test\TestCase\TestCase;
use Cake\Console\CommandInterface;
use Cake\Core\Plugin;
use Cake\Http\ServerRequest as Request;

/**
 * TestCommandTest class
 */
class TestCommandTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var string
     */
    protected array $fixtures = [
        'plugin.Bake.BakeArticles',
        'plugin.Bake.BakeArticlesBakeTags',
        'plugin.Bake.BakeComments',
        'plugin.Bake.BakeTags',
        'plugin.Bake.Authors',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setAppNamespace('Bake\Test\App');
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Test' . DS;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->getTableLocator()->clear();
    }

    /**
     * Test that with no args execute() outputs the types you can generate
     * tests for.
     *
     * @return void
     */
    public function testExecuteNoArgsPrintsTypeOptions()
    {
        $this->exec('bake test');

        $this->assertOutputContains('You must provide a class type');
        $this->assertOutputContains('1. Entity');
        $this->assertOutputContains('2. Table');
        $this->assertOutputContains('3. Controller');
        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
    }

    /**
     * Test that with no args execute() outputs the types you can generate
     * tests for.
     *
     * @return void
     */
    public function testExecuteOneArgPrintsClassOptions()
    {
        $this->exec('bake test entity');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertOutputContains('You must provide a class to bake');
    }

    /**
     * test execute with type and class name defined
     *
     * @return void
     */
    public function testExecuteWithTwoArgs()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/Model/Table/TestTaskTagTableTest.php',
        ];
        $this->exec('bake test Table TestTaskTag');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains(
            'class TestTaskTagTableTest extends TestCase',
            $this->generatedFiles[0]
        );
    }

    /**
     * test execute with plugin syntax
     *
     * @return void
     */
    public function testExecuteWithPluginName()
    {
        $this->_loadTestPlugin('TestBake');

        $this->generatedFiles = [
            ROOT . 'Plugin/TestBake/tests/TestCase/Model/Table/BakeArticlesTableTest.php',
        ];
        $this->exec('bake test table TestBake.BakeArticles');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains(
            'class BakeArticlesTableTest extends TestCase',
            $this->generatedFiles[0]
        );
        $this->assertFileContains(
            'namespace TestBake\Test\TestCase\Model\Table;',
            $this->generatedFiles[0]
        );
    }

    /**
     * test execute with type and class name defined
     *
     * @return void
     */
    public function testExecuteWithAll()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/Model/Table/ArticlesTableTest.php',
            ROOT . 'tests/TestCase/Model/Table/AuthorsTableTest.php',
            ROOT . 'tests/TestCase/Model/Table/BakeArticlesTableTest.php',
            ROOT . 'tests/TestCase/Model/Table/CategoryThreadsTableTest.php',
            ROOT . 'tests/TestCase/Model/Table/TemplateTaskCommentsTableTest.php',
            ROOT . 'tests/TestCase/Model/Table/HiddenFieldsTableTest.php',
            ROOT . 'tests/TestCase/Model/Table/ParseTestTableTest.php',
        ];
        $this->exec('bake test table --all');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
    }

    /**
     * Test generating class options for table.
     *
     * @return void
     */
    public function testOutputClassOptionsForTable()
    {
        $this->exec('bake test table');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertOutputContains('You must provide a class to bake a test for. Some possible options are:');
        $this->assertOutputContains('1. ArticlesTable');
        $this->assertOutputContains('2. AuthorsTable');
        $this->assertOutputContains('3. BakeArticlesTable');
        $this->assertOutputContains('4. CategoryThreadsTable');
        $this->assertOutputContains('5. HiddenFieldsTable');
        $this->assertOutputContains('6. ParseTestTable');
        $this->assertOutputContains('7. TemplateTaskCommentsTable');
        $this->assertOutputContains('Re-run your command as `cake bake Table <classname>`');
    }

    /**
     * Test generating class options for table.
     *
     * @return void
     */
    public function testOutputClassOptionsForTablePlugin()
    {
        $this->loadPlugins(['BakeTest' => ['path' => ROOT . 'Plugin' . DS . 'BakeTest' . DS]]);
        $this->exec('bake test table --plugin BakeTest');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertOutputContains('You must provide a class to bake a test for. Some possible options are:');
        $this->assertOutputContains('1. AuthorsTable');
        $this->assertOutputContains('2. BakeArticlesTable');
        $this->assertOutputContains('3. BakeTestCommentsTable');
        $this->assertOutputContains('4. CommentsTable');
    }

    /**
     * Test that method introspection pulls all relevant non parent class
     * methods into the test case.
     *
     * @return void
     */
    public function testMethodIntrospection()
    {
        $command = new TestCommand();
        $result = $command->getTestableMethods('Bake\Test\App\Model\Table\ArticlesTable');
        $expected = ['findpublished', 'dosomething', 'dosomethingelse'];
        $this->assertEquals($expected, array_map('strtolower', $result));
    }

    /**
     * test that the generation of fixtures works correctly.
     *
     * @return void
     */
    public function testFixtureArrayGenerationFromModel()
    {
        $command = new TestCommand();
        $subject = new ArticlesTable();
        $result = $command->generateFixtureList($subject);
        $expected = [
            'app.Articles',
            'app.Authors',
            'app.Tags',
            'app.ArticlesTags',
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
        $this->getTableLocator()->clear();
        $subject = new CategoryThreadsTable();
        $command = new TestCommand();
        $result = $command->generateFixtureList($subject);
        $expected = [
            'app.CategoryThreads',
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * test that the generation of fixtures works correctly.
     *
     * @return void
     */
    public function testFixtureGenerationFromController()
    {
        $subject = new PostsController(new Request());
        $command = new TestCommand();
        $result = $command->generateFixtureList($subject);
        $expected = [
            'app.Posts',
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
            ['Entity', 'ArticleEntity', 'App\Model\Entity\ArticleEntity'],
            ['Table', 'Posts', 'App\Model\Table\PostsTable'],
            ['Table', 'PostsTable', 'App\Model\Table\PostsTable'],
            ['Controller', 'Posts', 'App\Controller\PostsController'],
            ['Controller', 'PostsController', 'App\Controller\PostsController'],
            ['Behavior', 'Timestamp', 'App\Model\Behavior\TimestampBehavior'],
            ['Behavior', 'TimestampBehavior', 'App\Model\Behavior\TimestampBehavior'],
            ['Helper', 'Form', 'App\View\Helper\FormHelper'],
            ['Helper', 'FormHelper', 'App\View\Helper\FormHelper'],
            ['Cell', 'Example', 'App\View\Cell\ExampleCell'],
            ['Cell', 'ExampleCell', 'App\View\Cell\ExampleCell'],
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
        $this->setAppNamespace('App');

        $command = new TestCommand();
        $result = $command->getRealClassname($type, $name);
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
        $command = new TestCommand();
        $command->plugin = 'TestBake';

        $result = $command->getRealClassname('Helper', 'Asset');
        $expected = 'TestBake\View\Helper\AssetHelper';
        $this->assertSame($expected, $result);
    }

    /**
     * test resolving class names with prefix
     *
     * @return void
     */
    public function testGetRealClassnamePrefix()
    {
        $command = new TestCommand();
        $result = $command->getRealClassname('Controller', 'Posts', 'Api/Public');

        $expected = 'Bake\Test\App\Controller\Api\Public\PostsController';
        $this->assertSame($expected, $result);
    }

    /**
     * Test baking a test for a concrete model with fixtures arg
     *
     * @return void
     */
    public function testBakeFixturesParam()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/Model/Table/AuthorsTableTest.php',
        ];
        $this->exec('bake test table Authors --fixtures app.Posts,app.Comments,app.Users');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }

    /**
     * Test baking a test for a concrete model with no-fixtures arg
     *
     * @return void
     */
    public function testBakeNoFixtureParam()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/Model/Table/AuthorsTableTest.php',
        ];
        $this->exec('bake test table Authors --no-fixture');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }

    /**
     * Test baking a test for a cell.
     *
     * @return void
     */
    public function testBakeCellTest()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/View/Cell/ArticlesCellTest.php',
        ];
        $this->exec('bake test cell Articles');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }

    /**
     * Test baking a test for a command.
     *
     * @return void
     */
    public function testBakeCommandTest()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/Command/OtherExampleCommandTest.php',
        ];
        $this->exec('bake test command OtherExample');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }

    /**
     * Test baking a test for a concrete model.
     *
     * @return void
     */
    public function testBakeModelTest()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/Model/Table/ArticlesTableTest.php',
        ];
        $this->exec('bake test table Articles');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }

    /**
     * test baking controller test files
     *
     * @return void
     */
    public function testBakeControllerTest()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/Controller/PostsControllerTest.php',
        ];
        $this->exec('bake test controller PostsController');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }

    /**
     * test baking controller test files
     *
     * @return void
     */
    public function testBakeControllerWithoutModelTest()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/Controller/NoModelControllerTest.php',
        ];
        $this->exec('bake test controller NoModelController');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }

    /**
     * test baking controller test files
     *
     * @return void
     */
    public function testBakePrefixControllerTest()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/Controller/Admin/PostsControllerTest.php',
        ];
        $this->exec('bake test controller Admin\PostsController');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }

    /**
     * test baking controller test files with prefix CLI option
     *
     * @return void
     */
    public function testBakePrefixControllerTestWithCliOption()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/Controller/Admin/PostsControllerTest.php',
        ];
        $this->exec('bake test controller --prefix Admin PostsController');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }

    /**
     * test baking component test files,
     *
     * @return void
     */
    public function testBakeComponentTest()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/Controller/Component/AppleComponentTest.php',
        ];
        $this->exec('bake test component Apple');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }

    /**
     * test baking behavior test files,
     *
     * @return void
     */
    public function testBakeBehaviorTest()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/Model/Behavior/ExampleBehaviorTest.php',
        ];
        $this->exec('bake test behavior Example');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }

    /**
     * test baking helper test files,
     *
     * @return void
     */
    public function testBakeHelperTest()
    {
        $this->generatedFiles = [
            ROOT . 'tests/TestCase/View/Helper/ExampleHelperTest.php',
        ];
        $this->exec('bake test helper Example');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFiles[0]));
    }

    /**
     * Test baking an unknown class type.
     *
     * @return void
     */
    public function testBakeUnknownClass()
    {
        $this->exec('bake test Foo Example');

        $this->assertExitCode(CommandInterface::CODE_ERROR);
    }

    /**
     * test Constructor generation ensure that constructClasses is called for controllers
     *
     * @return void
     */
    public function testGenerateConstructor()
    {
        $command = new TestCommand();
        $result = $command->generateConstructor('Controller', 'PostsController');
        $expected = ['', '', ''];
        $this->assertEquals($expected, $result);

        $result = $command->generateConstructor('Table', 'App\Model\\Table\PostsTable');
        $expected = [
            "\$config = \$this->getTableLocator()->exists('Posts') ? [] : ['className' => PostsTable::class];",
            "\$this->getTableLocator()->get('Posts', \$config);",
            '',
        ];
        $this->assertEquals($expected, $result);

        $result = $command->generateConstructor('Helper', 'FormHelper');
        $expected = ['$view = new View();', 'new FormHelper($view);', ''];
        $this->assertEquals($expected, $result);

        $result = $command->generateConstructor('Entity', 'TestBake\Model\Entity\Article');
        $expected = ['', 'new Article();', ''];
        $this->assertEquals($expected, $result);

        $result = $command->generateConstructor('Form', 'TestBake\Form\ExampleForm');
        $expected = [
            '',
            'new ExampleForm();',
            '',
        ];
        $this->assertEquals($expected, $result);

        $result = $command->generateConstructor('Behavior', 'App\Model\Behavior\PostsBehavior');
        $expected = [
            '$table = new Table();',
            'new PostsBehavior($table);',
            '',
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
        $command = new TestCommand();
        $result = $command->generateUses('Table', 'App\Model\Table\PostsTable');
        $expected = [
            'App\Model\Table\PostsTable',
        ];
        $this->assertEquals($expected, $result);

        $result = $command->generateUses('Controller', 'App\Controller\PostsController');
        $expected = [
            'App\Controller\PostsController',
        ];
        $this->assertEquals($expected, $result);

        $result = $command->generateUses('Helper', 'App\View\Helper\FormHelper');
        $expected = [
            'Cake\View\View',
            'App\View\Helper\FormHelper',
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
        $command = new TestCommand();
        $result = $command->hasMockClass('Controller');
        $this->assertTrue($result);
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
            ['entity', 'App\Model\Entity\Article', 'TestCase/Model/Entity/ArticleTest.php'],
            ['table', 'App\Model\Table\PostsTable', 'TestCase/Model/Table/PostsTableTest.php'],
            ['helper', 'App\View\Helper\FormHelper', 'TestCase/View/Helper/FormHelperTest.php'],
            ['controller', 'App\Controller\PostsController', 'TestCase/Controller/PostsControllerTest.php'],
            ['behavior', 'App\Model\Behavior\TreeBehavior', 'TestCase/Model/Behavior/TreeBehaviorTest.php'],
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
        $this->setAppNamespace('App');
        $command = new TestCommand();
        $result = $command->testCaseFileName($type, $class);

        $this->assertPathEquals(ROOT . DS . 'tests' . DS . $expected, $result);
    }

    /**
     * Test filename generation for plugins.
     *
     * @return void
     */
    public function testTestCaseFileNamePlugin()
    {
        $this->loadPlugins([
            'TestTest' => [
                'path' => APP . 'Plugin' . DS . 'TestTest' . DS,
            ],
        ]);
        $this->generatedFiles = [
            APP . 'Plugin/TestTest/tests/TestCase/Model/Entity/ArticleTest.php',
        ];
        $this->exec('bake test entity --plugin TestTest Article');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
    }

    /**
     * Data provider for mapType() tests.
     *
     * @return array
     */
    public static function mapTypeProvider()
    {
        return [
            ['Controller', 'Controller'],
            ['Component', 'Controller\Component'],
            ['Table', 'Model\Table'],
            ['Entity', 'Model\Entity'],
            ['Behavior', 'Model\Behavior'],
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
        $command = new TestCommand();
        $this->assertEquals($expected, $command->mapType($original));
    }

    /**
     * Test docblock @ uses generated for test methods
     *
     * @return void
     */
    public function testGenerateUsesDocBlockController()
    {
        $testsPath = ROOT . 'tests' . DS;

        $this->exec('bake test --connection test controller Products', ['y']);

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileContains(
            '@uses \Bake\Test\App\Controller\ProductsController::index()',
            $testsPath . 'TestCase/Controller/ProductsControllerTest.php'
        );
    }

    /**
     * Test docblock @ uses generated for test methods
     *
     * @return void
     */
    public function testGenerateUsesDocBlockTable()
    {
        $testsPath = ROOT . 'tests' . DS;

        $this->generatedFiles = [
            $testsPath . 'TestCase/Model/Table/ProductsTableTest.php',
            $testsPath . 'TestCase/Controller/ProductsControllerTest.php',
        ];
        $this->exec('bake test --connection test table Products', ['y']);

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileContains(
            '@uses \Bake\Test\App\Model\Table\ProductsTable::validationDefault()',
            $testsPath . 'TestCase/Model/Table/ProductsTableTest.php'
        );
    }
}
