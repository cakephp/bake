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
use Bake\Test\TestCase\TestCase;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\ORM\TableRegistry;

/**
 * TemplateTaskTest class
 */
class TemplateTaskTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.Articles',
        'core.Tags',
        'core.ArticlesTags',
        'core.Posts',
        'core.Comments',
        'core.TestPluginComments',
        'plugin.Bake.BakeTemplateAuthors',
        'plugin.Bake.BakeTemplateRoles',
        'plugin.Bake.BakeTemplateProfiles',
        'plugin.Bake.CategoryThreads',
    ];

    /**
     * setUp method
     *
     * Ensure that the default template is used
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Template' . DS;

        Configure::write('App.namespace', 'Bake\Test\App');
        $this->_setupTask(['in', 'err', 'abort', 'createFile', '_stop']);

        TableRegistry::getTableLocator()->get('TemplateTaskComments', [
            'className' => 'Bake\Test\App\Model\Table\TemplateTaskCommentsTable',
        ]);
    }

    /**
     * Generate the mock objects used in tests.
     *
     * @param $methods
     * @return void
     */
    protected function _setupTask($methods)
    {
        $io = $this->getMockBuilder('Cake\Console\ConsoleIo')
            ->disableOriginalConstructor()
            ->getMock();

        $this->Task = $this->getMockBuilder('Bake\Shell\Task\TemplateTask')
            ->setMethods($methods)
            ->setConstructorArgs([$io])
            ->getMock();

        $this->Task->BakeTemplate = new BakeTemplateTask($io);
        $this->Task->Model = $this->getMockBuilder('Bake\Shell\Task\ModelTask')
            ->setConstructorArgs([$io])
            ->setMethods(['listUnskipped', 'execute', 'createFile'])
            ->getMock();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::getTableLocator()->clear();
        unset($this->Task);
    }

    /**
     * Test the controller() method.
     *
     * @return void
     */
    public function testController()
    {
        $this->Task->controller('Comments');
        $this->assertEquals('Comments', $this->Task->controllerName);
        $this->assertEquals(
            'Bake\Test\App\Controller\CommentsController',
            $this->Task->controllerClass
        );
    }

    /**
     * Test the controller() method.
     *
     * @param $name
     * @dataProvider nameVariations
     * @return void
     */
    public function testControllerVariations($name)
    {
        $this->Task->controller($name);
        $this->assertEquals('TemplateTaskComments', $this->Task->controllerName);
    }

    /**
     * Test controller method with plugins.
     *
     * @return void
     */
    public function testControllerPlugin()
    {
        $this->Task->params['plugin'] = 'BakeTest';
        $this->Task->controller('Tests');
        $this->assertEquals('Tests', $this->Task->controllerName);
        $this->assertEquals(
            'BakeTest\Controller\TestsController',
            $this->Task->controllerClass
        );
    }

    /**
     * Test controller method with prefixes.
     *
     * @return void
     */
    public function testControllerPrefix()
    {
        $this->Task->params['prefix'] = 'Admin';
        $this->Task->controller('Posts');
        $this->assertEquals('Posts', $this->Task->controllerName);
        $this->assertEquals(
            'Bake\Test\App\Controller\Admin\PostsController',
            $this->Task->controllerClass
        );

        $this->Task->params['plugin'] = 'BakeTest';
        $this->Task->controller('Comments');
        $this->assertEquals('Comments', $this->Task->controllerName);
        $this->assertEquals(
            'BakeTest\Controller\Admin\CommentsController',
            $this->Task->controllerClass
        );
    }

    /**
     * Test controller method with nested prefixes.
     *
     * @return void
     */
    public function testControllerPrefixNested()
    {
        $this->Task->params['prefix'] = 'Admin/Management';
        $this->Task->controller('Posts');
        $this->assertEquals('Posts', $this->Task->controllerName);
        $this->assertEquals(
            'Bake\Test\App\Controller\Admin\Management\PostsController',
            $this->Task->controllerClass
        );
    }

    /**
     * test controller with a non-conventional controller name
     *
     * @return void
     */
    public function testControllerWithOverride()
    {
        $this->Task->controller('Comments', 'Posts');
        $this->assertEquals('Posts', $this->Task->controllerName);
        $this->assertEquals(
            'Bake\Test\App\Controller\PostsController',
            $this->Task->controllerClass
        );
    }

    /**
     * Test the model() method.
     *
     * @return void
     */
    public function testModel()
    {
        $this->Task->model('Articles');
        $this->assertEquals('Articles', $this->Task->modelName);

        $this->Task->model('NotThere');
        $this->assertEquals('NotThere', $this->Task->modelName);
    }

    /**
     * Test model() method with plugins.
     *
     * @return void
     */
    public function testModelPlugin()
    {
        $this->Task->params['plugin'] = 'BakeTest';
        $this->Task->model('BakeTestComments');
        $this->assertEquals(
            'BakeTest.BakeTestComments',
            $this->Task->modelName
        );
    }

    /**
     * Test getPath()
     *
     * @return void
     */
    public function testGetPath()
    {
        $this->Task->controllerName = 'Posts';

        $result = $this->Task->getPath();
        $this->assertPathEquals(APP . 'Template/Posts/', $result);

        $this->Task->params['prefix'] = 'admin';
        $result = $this->Task->getPath();
        $this->assertPathEquals(APP . 'Template/Admin/Posts/', $result);

        $this->Task->params['prefix'] = 'admin/management';
        $result = $this->Task->getPath();
        $this->assertPathEquals(APP . 'Template/Admin/Management/Posts/', $result);

        $this->Task->params['prefix'] = 'Admin/management';
        $result = $this->Task->getPath();
        $this->assertPathEquals(APP . 'Template/Admin/Management/Posts/', $result);
    }

    /**
     * Test getPath with plugins.
     *
     * @return void
     */
    public function testGetPathPlugin()
    {
        $this->Task->controllerName = 'Posts';

        $pluginPath = APP . 'Plugin/TestTemplate/';
        $this->loadPlugins([
            'TestTemplate' => ['path' => $pluginPath],
        ]);

        $this->Task->params['plugin'] = $this->Task->plugin = 'TestTemplate';
        $result = $this->Task->getPath();
        $this->assertPathEquals($pluginPath . 'src/Template/Posts/', $result);

        $this->Task->params['prefix'] = 'admin';
        $result = $this->Task->getPath();
        $this->assertPathEquals($pluginPath . 'src/Template/Admin/Posts/', $result);

        $this->removePlugins(['TestTemplate']);
    }

    /**
     * Test getContent and parsing of Templates.
     *
     * @return void
     */
    public function testGetContent()
    {
        $namespace = Configure::read('App.namespace');
        $vars = [
            'modelClass' => 'TestTemplateModel',
            'entityClass' => $namespace . '\Model\Entity\TestTemplateModel',
            'schema' => TableRegistry::getTableLocator()->get('TemplateTaskComments')->getSchema(),
            'primaryKey' => ['id'],
            'displayField' => 'name',
            'singularVar' => 'testTemplateModel',
            'pluralVar' => 'testTemplateModels',
            'singularHumanName' => 'Test Template Model',
            'pluralHumanName' => 'Test Template Models',
            'fields' => ['id', 'name', 'body'],
            'associations' => [],
            'keyFields' => [],
            'namespace' => $namespace,
        ];
        $result = $this->Task->getContent('view', $vars);
        $this->assertSameAsFile(__FUNCTION__ . '.ctp', $result);
    }

    /**
     * Test getContent with associations
     *
     * @return void
     */
    public function testGetContentAssociations()
    {
        $namespace = Configure::read('App.namespace');
        $vars = [
            'modelClass' => 'TemplateTaskComments',
            'entityClass' => $namespace . '\Model\Entity\TemplateTaskComment',
            'schema' => TableRegistry::getTableLocator()->get('TemplateTaskComments')->getSchema(),
            'primaryKey' => ['id'],
            'displayField' => 'name',
            'singularVar' => 'templateTaskComment',
            'pluralVar' => 'templateTaskComments',
            'singularHumanName' => 'Template Task Comment',
            'pluralHumanName' => 'Template Task Comments',
            'fields' => ['id', 'name', 'body'],
            'associations' => [
                'belongsTo' => [
                    'Authors' => [
                        'property' => 'author',
                        'variable' => 'author',
                        'primaryKey' => ['id'],
                        'displayField' => 'name',
                        'foreignKey' => 'author_id',
                        'alias' => 'Authors',
                        'controller' => 'TemplateTaskAuthors',
                        'fields' => ['name'],
                    ],
                ],
            ],
            'keyFields' => [],
            'namespace' => $namespace,
        ];
        $result = $this->Task->getContent('view', $vars);
        $this->assertSameAsFile(__FUNCTION__ . '.ctp', $result);
    }

    /**
     * Test getContent with no pk
     *
     * @return void
     */
    public function testGetContentWithNoPrimaryKey()
    {
        $namespace = Configure::read('App.namespace');
        $vars = [
            'modelClass' => 'TestTemplateModel',
            'entityClass' => $namespace . '\Model\Entity\TestTemplateModel',
            'schema' => TableRegistry::getTableLocator()->get('TemplateTaskComments')->getSchema(),
            'primaryKey' => [],
            'displayField' => 'name',
            'singularVar' => 'testTemplateModel',
            'pluralVar' => 'testTemplateModels',
            'singularHumanName' => 'Test Template Model',
            'pluralHumanName' => 'Test Template Models',
            'fields' => ['id', 'name', 'body'],
            'associations' => [],
            'keyFields' => [],
            'namespace' => $namespace,
        ];
        $this->Task->expects($this->once())
            ->method('abort')
            ->with($this->stringContains('Cannot generate views for models'));

        $result = $this->Task->getContent('view', $vars);
        $this->assertFalse($result);
    }

    /**
     * test getContent() using a routing prefix action.
     *
     * @return void
     */
    public function testGetContentWithRoutingPrefix()
    {
        $namespace = Configure::read('App.namespace');
        $vars = [
            'modelClass' => 'TestTemplateModel',
            'entityClass' => $namespace . '\Model\Entity\TestTemplateModel',
            'schema' => TableRegistry::getTableLocator()->get('TemplateTaskComments')->getSchema(),
            'primaryKey' => ['id'],
            'displayField' => 'name',
            'singularVar' => 'testTemplateModel',
            'pluralVar' => 'testTemplateModels',
            'singularHumanName' => 'Test Template Model',
            'pluralHumanName' => 'Test Template Models',
            'fields' => ['id', 'name', 'body'],
            'keyFields' => [],
            'associations' => [],
            'namespace' => $namespace,
        ];
        $this->Task->params['prefix'] = 'Admin';
        $result = $this->Task->getContent('view', $vars);
        $this->assertSameAsFile(__FUNCTION__ . '-view.ctp', $result);

        $result = $this->Task->getContent('add', $vars);
        $this->assertSameAsFile(__FUNCTION__ . '-add.ctp', $result);
    }

    /**
     * test Bake method
     *
     * @return void
     */
    public function testBakeView()
    {
        $this->generatedFile = APP . 'Template/Authors/view.ctp';
        $this->exec('bake template authors view');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.ctp', $result);
    }

    /**
     * test baking an edit file
     *
     * @return void
     */
    public function testBakeEdit()
    {
        $this->generatedFile = APP . 'Template/Authors/edit.ctp';
        $this->exec('bake template authors edit');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.ctp', $result);
    }

    /**
     * test baking an index
     *
     * @return void
     */
    public function testBakeIndex()
    {
        $this->generatedFile = APP . 'Template/TemplateTaskComments/index.ctp';
        $this->exec('bake template template_task_comments index');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.ctp', $result);
    }

    /**
     * test bake template with index limit overwrite
     *
     * @return void
     */
    public function testBakeIndexWithIndexLimit()
    {
        $this->generatedFile = APP . 'Template/TemplateTaskComments/index.ctp';
        $this->exec('bake template template_task_comments --index-columns 3 index');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.ctp', $result);
    }

    /**
     * test Bake with plugins
     *
     * @return void
     */
    public function testBakeIndexPlugin()
    {
        $this->_loadTestPlugin('BakeTest');
        $path = Plugin::path('BakeTest');

        // Setup association to ensure properties don't have dots
        $model = TableRegistry::getTableLocator()->get('BakeTest.Comments');
        $model->belongsTo('Articles');

        $this->generatedFile = $path . 'src/Template/Comments/index.ctp';
        $this->exec('bake template BakeTest.comments index');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertFileContains('$comment->article->id', $this->generatedFile);
    }

    /**
     * Ensure that models in a tree don't include form fields for lft/rght
     *
     * @return void
     */
    public function testBakeTreeNoLftOrRght()
    {
        $this->Task->controllerName = 'CategoryThreads';
        $this->Task->modelName = 'Bake\Test\App\Model\Table\CategoryThreadsTable';

        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Template/CategoryThreads/add.ctp'),
                $this->logicalNot(
                    $this->logicalAnd(
                        $this->stringContains('rght'),
                        $this->stringContains('lft')
                    )
                )
            );
        $this->Task->expects($this->at(1))
            ->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Template/CategoryThreads/index.ctp'),
                $this->logicalNot(
                    $this->logicalAnd(
                        $this->stringContains('rght'),
                        $this->stringContains('lft')
                    )
                )
            );

        $this->Task->bake('add', true);
        $this->Task->bake('index', true);
    }

    /**
     * Ensure that models associated with themselves do not have action
     * links generated.
     *
     * @return void
     */
    public function testBakeSelfAssociationsNoNavLinks()
    {
        $this->Task->controllerName = 'CategoryThreads';
        $this->Task->modelName = 'Bake\Test\App\Model\Table\CategoryThreadsTable';

        $this->Task->expects($this->once())
            ->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Template/CategoryThreads/index.ctp'),
                $this->logicalNot(
                    $this->logicalAnd(
                        $this->stringContains('New Parent Category Thread'),
                        $this->stringContains('List Parent Category Threads'),
                        $this->stringContains('rght'),
                        $this->stringContains('lft')
                    )
                )
            );

        $this->Task->bake('index', true);
    }

    /**
     * Ensure that models associated with themselves do not have action
     * links generated.
     *
     * @return void
     */
    public function testBakeSelfAssociationsRelatedAssociations()
    {
        $this->generatedFile = APP . 'Template/CategoryThreads/view.ctp';
        $this->exec('bake template category_threads view');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);

        $this->assertFileContains('Related Category Threads', $this->generatedFile);
        $this->assertFileContains('Parent Category Threads', $this->generatedFile);
    }

    /**
     * test that baking a view with no template doesn't make a file.
     *
     * @return void
     */
    public function testBakeWithNoTemplate()
    {
        $this->exec('bake template template_task_comments delete');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileNotExists(APP . 'Template/TemplateTaskComments/delete.ctp');
    }

    /**
     * Test execute no args.
     *
     * @return void
     */
    public function testMainNoArgs()
    {
        $this->exec('bake template');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('Possible tables to bake view templates for based on your current database:');
        $this->assertOutputContains('- Comments');
        $this->assertOutputContains('- Articles');
    }

    /**
     * Test all() calls execute
     *
     * @return void
     */
    public function testAllCallsMain()
    {
        $this->_setupTask(['in', 'err', 'createFile', 'main', '_stop']);

        $this->Task->Model->expects($this->once())
            ->method('listUnskipped')
            ->will($this->returnValue(['comments', 'articles']));

        $this->Task->expects($this->exactly(2))
            ->method('main');
        $this->Task->expects($this->at(0))
            ->method('main')
            ->with('comments');
        $this->Task->expects($this->at(1))
            ->method('main')
            ->with('articles');

        $this->Task->all();
    }

    /**
     * test `cake bake view $controller view`
     *
     * @return void
     */
    public function testMainWithActionParam()
    {
        $this->generatedFile = APP . 'Template/TemplateTaskComments/view.ctp';
        $this->exec('bake template TemplateTaskComments view');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertFileNotExists(
            APP . 'Template/TemplateTaskComments/edit.ctp',
            'no extra files'
        );
        $this->assertFileNotExists(
            APP . 'Template/TemplateTaskComments/add.ctp',
            'no extra files'
        );
    }

    /**
     * test `cake bake view $controller`
     * Ensure that views are only baked for actions that exist in the controller.
     *
     * @return void
     */
    public function testMainWithExistingController()
    {
        $this->generatedFiles = [
            APP . 'Template/TemplateTaskComments/index.ctp',
            APP . 'Template/TemplateTaskComments/add.ctp',
        ];
        $this->exec('bake template TemplateTaskComments');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileNotExists(
            APP . 'Template/TemplateTaskComments/edit.ctp',
            'no extra files'
        );
        $this->assertFileNotExists(
            APP . 'Template/TemplateTaskComments/view.ctp',
            'no extra files'
        );
    }

    /**
     * test that plugin.name works.
     *
     * @return void
     */
    public function testMainWithPluginName()
    {
        $this->_loadTestPlugin('TestBake');
        $path = Plugin::path('TestBake');

        $this->generatedFile = $path . 'src/Template/Comments/index.ctp';
        $this->exec('bake template --connection test TestBake.Comments index');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertFileNotExists(
            $path . 'src/Template/Comments/view.ctp',
            'No other templates made'
        );
    }

    /**
     * static dataprovider for test cases
     *
     * @return void
     */
    public static function nameVariations()
    {
        return [['TemplateTaskComments'], ['template_task_comments']];
    }

    /**
     * test `cake bake view $table --controller Blog`
     *
     * @return void
     */
    public function testMainWithControllerFlag()
    {
        $this->generatedFiles = [
            APP . 'Template/Blog/index.ctp',
            APP . 'Template/Blog/view.ctp',
            APP . 'Template/Blog/add.ctp',
            APP . 'Template/Blog/edit.ctp',
        ];
        $this->exec('bake template --controller Blog Posts');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
    }

    /**
     * test `cake bake view $controller --prefix Admin`
     *
     * @return void
     */
    public function testMainWithControllerAndAdminFlag()
    {
        $this->generatedFiles = [
            APP . 'Template/Admin/Posts/index.ctp',
            APP . 'Template/Admin/Posts/add.ctp',
        ];
        $this->exec('bake template --prefix Admin Posts');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
    }

    /**
     * test `cake bake view posts index list`
     *
     * @return void
     */
    public function testMainWithAlternateTemplates()
    {
        $this->generatedFile = APP . 'Template/TemplateTaskComments/list.ctp';
        $this->exec('bake template TemplateTaskComments index list');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertFileContains('Template Task Comments', $this->generatedFile);
    }
}
