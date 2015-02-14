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
use Cake\Controller\Controller;
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
        'core.articles',
        'core.tags',
        'core.articles_tags',
        'core.posts',
        'core.comments',
        'core.test_plugin_comments',
        'plugin.bake.category_threads',
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
        $this->_setupTask(['in', 'err', 'error', 'createFile', '_stop']);

        TableRegistry::get('TemplateTaskComments', [
            'className' => __NAMESPACE__ . '\TemplateTaskCommentsTable',
        ]);
    }

    /**
     * Generate the mock objects used in tests.
     *
     * @return void
     */
    protected function _setupTask($methods)
    {
        $io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Task = $this->getMock(
            'Bake\Shell\Task\TemplateTask',
            $methods,
            [$io]
        );
        $this->Task->BakeTemplate = new BakeTemplateTask($io);
        $this->Task->Model = $this->getMock('Bake\Shell\Task\ModelTask', [], [$io]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
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
        Plugin::load('TestTemplate', ['path' => $pluginPath]);

        $this->Task->params['plugin'] = $this->Task->plugin = 'TestTemplate';
        $result = $this->Task->getPath();
        $this->assertPathEquals($pluginPath . 'src/Template/Posts/', $result);

        $this->Task->params['prefix'] = 'admin';
        $result = $this->Task->getPath();
        $this->assertPathEquals($pluginPath . 'src/Template/Admin/Posts/', $result);

        Plugin::unload('TestTemplate');
    }

    /**
     * Test getContent and parsing of Templates.
     *
     * @return void
     */
    public function testGetContent()
    {
        $vars = [
            'modelClass' => 'TestTemplateModel',
            'schema' => TableRegistry::get('TemplateTaskComments')->schema(),
            'primaryKey' => ['id'],
            'displayField' => 'name',
            'singularVar' => 'testTemplateModel',
            'pluralVar' => 'testTemplateModels',
            'singularHumanName' => 'Test Template Model',
            'pluralHumanName' => 'Test Template Models',
            'fields' => ['id', 'name', 'body'],
            'associations' => [],
            'keyFields' => [],
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
        $vars = [
            'modelClass' => 'TemplateTaskComments',
            'schema' => TableRegistry::get('TemplateTaskComments')->schema(),
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
                    ]
                ]
            ],
            'keyFields' => [],
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
        $vars = [
            'modelClass' => 'TestTemplateModel',
            'schema' => TableRegistry::get('TemplateTaskComments')->schema(),
            'primaryKey' => [],
            'displayField' => 'name',
            'singularVar' => 'testTemplateModel',
            'pluralVar' => 'testTemplateModels',
            'singularHumanName' => 'Test Template Model',
            'pluralHumanName' => 'Test Template Models',
            'fields' => ['id', 'name', 'body'],
            'associations' => [],
            'keyFields' => [],
        ];
        $this->Task->expects($this->once())
            ->method('error')
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
        $vars = [
            'modelClass' => 'TestTemplateModel',
            'schema' => TableRegistry::get('TemplateTaskComments')->schema(),
            'primaryKey' => ['id'],
            'displayField' => 'name',
            'singularVar' => 'testTemplateModel',
            'pluralVar' => 'testTemplateModels',
            'singularHumanName' => 'Test Template Model',
            'pluralHumanName' => 'Test Template Models',
            'fields' => ['id', 'name', 'body'],
            'keyFields' => [],
            'associations' => []
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
    public function testBakeTemplate()
    {
        $this->Task->controllerName = 'TemplateTaskComments';
        $this->Task->modelName = 'TemplateTaskComments';
        $this->Task->controllerClass = __NAMESPACE__ . '\TemplateTaskCommentsController';

        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Template/TemplateTaskComments/view.ctp')
            );

        $result = $this->Task->bake('view', true);
        $this->assertSameAsFile(__FUNCTION__ . '.ctp', $result);
    }

    /**
     * test baking an edit file
     *
     * @return void
     */
    public function testBakeEdit()
    {
        $this->Task->controllerName = 'TemplateTaskComments';
        $this->Task->modelName = 'TemplateTaskComments';
        $this->Task->controllerClass = __NAMESPACE__ . '\TemplateTaskCommentsController';

        $this->Task->expects($this->at(0))->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Template/TemplateTaskComments/edit.ctp')
            );
        $result = $this->Task->bake('edit', true);
        $this->assertSameAsFile(__FUNCTION__ . '.ctp', $result);
    }

    /**
     * test baking an index
     *
     * @return void
     */
    public function testBakeIndex()
    {
        $this->Task->controllerName = 'TemplateTaskComments';
        $this->Task->modelName = 'TemplateTaskComments';
        $this->Task->controllerClass = __NAMESPACE__ . '\TemplateTaskCommentsController';

        $this->Task->expects($this->at(0))->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Template/TemplateTaskComments/index.ctp')
            );
        $result = $this->Task->bake('index', true);
        $this->assertSameAsFile(__FUNCTION__ . '.ctp', $result);
    }

    /**
     * test Bake with plugins
     *
     * @return void
     */
    public function testBakeIndexPlugin()
    {
        $this->Task->controllerName = 'TemplateTaskComments';
        $this->Task->modelName = 'BakeTest.BakeTestComments';
        $this->Task->controllerClass = __NAMESPACE__ . '\TemplateTaskCommentsController';
        $table = TableRegistry::get('BakeTest.BakeTestComments');
        $table->belongsTo('Articles');

        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Template/TemplateTaskComments/index.ctp'),
                $this->stringContains('$templateTaskComment->article->id')
            );

        $this->Task->bake('index', true);
    }

    /**
     * Ensure that models associated with themselves do not have action
     * links generated.
     *
     * @return void
     */
    public function testBakeSelfAssociations()
    {
        $this->Task->controllerName = 'CategoryThreads';
        $this->Task->modelName = 'Bake\Test\App\Model\Table\CategoryThreadsTable';

        $this->Task->expects($this->once())
            ->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Template/CategoryThreads/index.ctp'),
                $this->logicalNot($this->stringContains('ParentCategoryThread'))
            );

        $this->Task->bake('index', true);
    }

    /**
     * test that baking a view with no template doesn't make a file.
     *
     * @return void
     */
    public function testBakeWithNoTemplate()
    {
        $this->Task->controllerName = 'TemplateTaskComments';
        $this->Task->modelName = 'TemplateTaskComments';
        $this->Task->controllerClass = __NAMESPACE__ . '\TemplateTaskCommentsController';

        $this->Task->expects($this->never())->method('createFile');
        $this->Task->bake('delete', true);
    }

    /**
     * test bake actions baking multiple actions.
     *
     * @return void
     */
    public function testBakeActions()
    {
        $this->Task->controllerName = 'TemplateTaskComments';
        $this->Task->modelName = 'TemplateTaskComments';
        $this->Task->controllerClass = __NAMESPACE__ . '\TemplateTaskCommentsController';

        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Template/TemplateTaskComments/view.ctp'),
                $this->stringContains('Template Task Comments')
            );
        $this->Task->expects($this->at(1))->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Template/TemplateTaskComments/edit.ctp'),
                $this->stringContains('Edit Template Task Comment')
            );
        $this->Task->expects($this->at(2))->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Template/TemplateTaskComments/index.ctp'),
                $this->stringContains('TemplateTaskComment')
            );

        $this->Task->bakeActions(['view', 'edit', 'index'], []);
    }

    /**
     * test baking a customAction (non crud)
     *
     * @return void
     */
    public function testCustomAction()
    {
        $this->Task->controllerName = 'TemplateTaskComments';
        $this->Task->modelName = 'TemplateTaskComments';
        $this->Task->controllerClass = __NAMESPACE__ . '\TemplateTaskCommentsController';

        $this->Task->expects($this->any())->method('in')
            ->will($this->onConsecutiveCalls('', 'my_action', 'y'));

        $this->Task->expects($this->once())->method('createFile')
            ->with(
                $this->_normalizePath(APP . 'Template/TemplateTaskComments/my_action.ctp')
            );

        $this->Task->customAction();
    }

    /**
     * Test execute no args.
     *
     * @return void
     */
    public function testMainNoArgs()
    {
        $this->_setupTask(['in', 'err', 'bake', 'createFile', '_stop']);

        $this->Task->Model->expects($this->once())
            ->method('listAll')
            ->will($this->returnValue(['comments', 'articles']));

        $this->Task->expects($this->never())
            ->method('bake');

        $this->Task->main();
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
            ->method('listAll')
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
        $this->_setupTask(['in', 'err', 'createFile', 'bake', '_stop']);

        $this->Task->expects($this->once())
            ->method('bake')
            ->with('view', true);

        $this->Task->main('TemplateTaskComments', 'view');
    }

    /**
     * test `cake bake view $controller`
     * Ensure that views are only baked for actions that exist in the controller.
     *
     * @return void
     */
    public function testMainWithController()
    {
        $this->_setupTask(['in', 'err', 'createFile', 'bake', '_stop']);

        $this->Task->expects($this->exactly(4))
            ->method('bake');

        $this->Task->expects($this->at(0))
            ->method('bake')
            ->with('index', $this->anything());

        $this->Task->expects($this->at(1))
            ->method('bake')
            ->with('view', $this->anything());

        $this->Task->expects($this->at(2))
            ->method('bake')
            ->with('add', $this->anything());

        $this->Task->expects($this->at(3))
            ->method('bake')
            ->with('edit', $this->anything());

        $this->Task->main('TemplateTaskComments');
    }

    /**
     * test that plugin.name works.
     *
     * @return void
     */
    public function testMainWithPluginName()
    {
        // Populate the table registry with a "plugin" model
        TableRegistry::get('TestTemplate.TemplateTaskComments', [
            'className' => __NAMESPACE__ . '\TemplateTaskCommentsTable',
        ]);

        $this->_setupTask(['in', 'err', 'createFile']);

        $this->Task->connection = 'test';
        $filename = $this->_normalizePath(
            APP . 'Plugin/TestTemplate/src/Template/TemplateTaskComments/index.ctp'
        );

        Plugin::load('TestTemplate', ['path' => APP . 'Plugin/TestTemplate/']);

        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with($filename);
        $this->Task->main('TestTemplate.TemplateTaskComments');
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
        $this->Task->params['controller'] = 'Blog';

        $this->Task->expects($this->exactly(4))
            ->method('createFile');

        $templates = ['index.ctp', 'view.ctp', 'add.ctp', 'edit.ctp'];
        foreach ($templates as $i => $template) {
            $this->Task->expects($this->at($i))->method('createFile')
                ->with(
                    $this->_normalizePath(APP . 'Template/Blog/' . $template)
                );
        }
        $this->Task->main('Posts');
    }

    /**
     * test `cake bake view $controller --prefix Admin`
     *
     * @return void
     */
    public function testMainWithControllerAndAdminFlag()
    {
        $this->Task->params['prefix'] = 'Admin';

        $this->Task->expects($this->exactly(2))
            ->method('createFile');

        $templates = ['index.ctp', 'add.ctp'];
        foreach ($templates as $i => $template) {
            $this->Task->expects($this->at($i))->method('createFile')
                ->with(
                    $this->_normalizePath(APP . 'Template/Admin/Posts/' . $template)
                );
        }
        $this->Task->main('Posts');
    }

    /**
     * test `cake bake view posts index list`
     *
     * @return void
     */
    public function testMainWithAlternateTemplates()
    {
        $this->_setupTask(['in', 'err', 'createFile', 'bake', '_stop']);

        $this->Task->connection = 'test';
        $this->Task->params = [];

        $this->Task->expects($this->once())
            ->method('bake')
            ->with('list', true);
        $this->Task->main('TemplateTaskComments', 'index', 'list');
    }
}
