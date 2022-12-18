<?php
declare(strict_types=1);

/**
 * CakePHP : Rapid Development Framework (https://cakephp.org)
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

use Bake\Command\TemplateCommand;
use Bake\Test\App\Model\Table\BakeArticlesTable;
use Bake\Test\TestCase\TestCase;
use Cake\Console\Arguments;
use Cake\Console\CommandInterface;
use Cake\Console\ConsoleIo;
use Cake\Console\Exception\StopException;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\View\Exception\MissingTemplateException;

/**
 * TemplateCommand test
 */
class TemplateCommandTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'plugin.Bake.Articles',
        'plugin.Bake.Tags',
        'plugin.Bake.ArticlesTags',
        'plugin.Bake.Posts',
        'plugin.Bake.Comments',
        'plugin.Bake.BakeArticles',
        'plugin.Bake.BakeTemplateAuthors',
        'plugin.Bake.BakeTemplateRoles',
        'plugin.Bake.BakeTemplateProfiles',
        'plugin.Bake.CategoryThreads',
        'plugin.Bake.HiddenFields',
    ];

    /**
     * setUp method
     *
     * Ensure that the default template is used
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Template' . DS;

        $this->setAppNamespace('Bake\Test\App');

        $this->getTableLocator()->get('TemplateTaskComments', [
            'className' => 'Bake\Test\App\Model\Table\TemplateTaskCommentsTable',
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->getTableLocator()->clear();
    }

    /**
     * Test the controller() method.
     *
     * @return void
     */
    public function testController()
    {
        $command = new TemplateCommand();
        $args = new Arguments([], [], []);
        $command->controller($args, 'Comments');
        $this->assertSame('Comments', $command->controllerName);
        $this->assertSame(
            'Bake\Test\App\Controller\CommentsController',
            $command->controllerClass
        );
    }

    /**
     * Test the controller() method.
     *
     * @param string $name
     * @dataProvider nameVariations
     * @return void
     */
    public function testControllerVariations($name)
    {
        $command = new TemplateCommand();
        $args = new Arguments([], [], []);
        $command->controller($args, $name);
        $this->assertSame('TemplateTaskComments', $command->controllerName);
    }

    /**
     * Test controller method with plugins.
     *
     * @return void
     */
    public function testControllerPlugin()
    {
        $command = new TemplateCommand();
        $command->plugin = 'BakeTest';
        $args = new Arguments([], [], []);
        $command->controller($args, 'Tests');

        $this->assertSame('Tests', $command->controllerName);
        $this->assertSame(
            'BakeTest\Controller\TestsController',
            $command->controllerClass
        );
    }

    /**
     * Test controller method with prefixes.
     *
     * @return void
     */
    public function testControllerPrefix()
    {
        $command = new TemplateCommand();

        $args = new Arguments([], ['prefix' => 'Admin'], []);
        $command->controller($args, 'Posts');
        $this->assertSame('Posts', $command->controllerName);
        $this->assertSame(
            'Bake\Test\App\Controller\Admin\PostsController',
            $command->controllerClass
        );

        $command->plugin = 'BakeTest';
        $command->controller($args, 'Comments');
        $this->assertSame('Comments', $command->controllerName);
        $this->assertSame(
            'BakeTest\Controller\Admin\CommentsController',
            $command->controllerClass
        );
    }

    /**
     * Test controller method with nested prefixes.
     *
     * @return void
     */
    public function testControllerPrefixNested()
    {
        $command = new TemplateCommand();
        $args = new Arguments([], ['prefix' => 'Admin/Management'], []);

        $command->controller($args, 'Posts');
        $this->assertSame('Posts', $command->controllerName);
        $this->assertSame(
            'Bake\Test\App\Controller\Admin\Management\PostsController',
            $command->controllerClass
        );
    }

    /**
     * test controller with a non-conventional controller name
     *
     * @return void
     */
    public function testControllerWithOverride()
    {
        $command = new TemplateCommand();
        $args = new Arguments([], [], []);

        $command->controller($args, 'Comments', 'Posts');
        $this->assertSame('Posts', $command->controllerName);
        $this->assertSame(
            'Bake\Test\App\Controller\PostsController',
            $command->controllerClass
        );
    }

    /**
     * Test the model() method.
     *
     * @return void
     */
    public function testModel()
    {
        $command = new TemplateCommand();
        $command->model('Articles');
        $this->assertSame('Articles', $command->modelName);

        $command->model('NotThere');
        $this->assertSame('NotThere', $command->modelName);
    }

    /**
     * Test model() method with plugins.
     *
     * @return void
     */
    public function testModelPlugin()
    {
        $command = new TemplateCommand();
        $command->plugin = 'BakeTest';
        $command->model('BakeTestComments');
        $this->assertSame(
            'BakeTest.BakeTestComments',
            $command->modelName
        );
    }

    /**
     * Test getTemplatePath()
     *
     * @return void
     */
    public function testGetTemplatePath()
    {
        $command = new TemplateCommand();
        $command->controllerName = 'Posts';
        $args = new Arguments([], [], []);

        $result = $command->getTemplatePath($args);
        $this->assertPathEquals(ROOT . 'templates/Posts/', $result);

        $args = new Arguments([], ['prefix' => 'admin'], []);
        $result = $command->getTemplatePath($args);
        $this->assertPathEquals(ROOT . 'templates/Admin/Posts/', $result);

        $args = new Arguments([], ['prefix' => 'admin/management'], []);
        $result = $command->getTemplatePath($args);
        $this->assertPathEquals(ROOT . 'templates/Admin/Management/Posts/', $result);

        $args = new Arguments([], ['prefix' => 'Admin/management'], []);
        $result = $command->getTemplatePath($args);
        $this->assertPathEquals(ROOT . 'templates/Admin/Management/Posts/', $result);
    }

    /**
     * Test getPath with plugins.
     *
     * @return void
     */
    public function testGetTemplatePathPlugin()
    {
        $pluginPath = APP . 'Plugin/TestTemplate/';
        $this->loadPlugins(['TestTemplate' => ['path' => $pluginPath]]);

        $command = new TemplateCommand();
        $command->controllerName = 'Posts';
        $command->plugin = 'TestTemplate';

        // Use this->plugin as plugin could be in the name arg
        $args = new Arguments([], [], []);
        $result = $command->getTemplatePath($args);
        $this->assertPathEquals($pluginPath . 'templates/Posts/', $result);

        // Use this->plugin as plugin could be in the name arg
        $args = new Arguments([], ['prefix' => 'admin'], []);
        $result = $command->getTemplatePath($args);
        $this->assertPathEquals($pluginPath . 'templates/Admin/Posts/', $result);

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
            'schema' => $this->getTableLocator()->get('TemplateTaskComments')->getSchema(),
            'primaryKey' => ['id'],
            'displayField' => 'name',
            'singularVar' => 'testTemplateModel',
            'pluralVar' => 'testTemplateModels',
            'singularHumanName' => 'Test Template Model',
            'pluralHumanName' => 'Test Template Models',
            'fields' => ['id', 'name', 'body'],
            'hidden' => ['token', 'password', 'passwd'],
            'associations' => [],
            'keyFields' => [],
            'namespace' => $namespace,
        ];
        $command = new TemplateCommand();
        $args = new Arguments([], [], []);
        $io = $this->createMock(ConsoleIo::class);
        $result = $command->getContent($args, $io, 'view', $vars);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
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
            'schema' => $this->getTableLocator()->get('TemplateTaskComments')->getSchema(),
            'primaryKey' => ['id'],
            'displayField' => 'name',
            'singularVar' => 'templateTaskComment',
            'pluralVar' => 'templateTaskComments',
            'singularHumanName' => 'Template Task Comment',
            'pluralHumanName' => 'Template Task Comments',
            'fields' => ['id', 'name', 'body'],
            'hidden' => ['token', 'password', 'passwd'],
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

        $command = new TemplateCommand();
        $args = new Arguments([], [], []);
        $io = $this->createMock(ConsoleIo::class);
        $result = $command->getContent($args, $io, 'view', $vars);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
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
            'schema' => $this->getTableLocator()->get('TemplateTaskComments')->getSchema(),
            'primaryKey' => [],
            'displayField' => 'name',
            'singularVar' => 'testTemplateModel',
            'pluralVar' => 'testTemplateModels',
            'singularHumanName' => 'Test Template Model',
            'pluralHumanName' => 'Test Template Models',
            'fields' => ['id', 'name', 'body'],
            'hidden' => ['token', 'password', 'passwd'],
            'associations' => [],
            'keyFields' => [],
            'namespace' => $namespace,
        ];

        $this->expectException(StopException::class);
        $io = $this->createMock(ConsoleIo::class);
        $io->expects($this->once())
            ->method('error')
            ->with('Cannot generate views for models with no primary key');

        $command = new TemplateCommand();
        $args = new Arguments([], [], []);
        $command->getContent($args, $io, 'view', $vars);
    }

    /**
     * test getContent() using a routing prefix action.
     *
     * @return void
     */
    public function testGetContentWithRoutingPrefix()
    {
        $namespace = Configure::read('App.namespace');

        $modelObject = $this->getTableLocator()->get('BakeArticles', [
            'className' => BakeArticlesTable::class,
        ]);

        $vars = [
            'modelClass' => 'BakeArticles',
            'entityClass' => $namespace . '\Model\Entity\BakeArticle',
            'modelObject' => $modelObject,
            'schema' => $modelObject->getSchema(),
            'primaryKey' => ['id'],
            'displayField' => 'title',
            'singularVar' => 'testTemplateModel',
            'pluralVar' => 'testTemplateModels',
            'singularHumanName' => 'Test Template Model',
            'pluralHumanName' => 'Test Template Models',
            'fields' => ['id', 'title', 'body'],
            'hidden' => ['token', 'password', 'passwd'],
            'keyFields' => [],
            'associations' => [],
            'namespace' => $namespace,
        ];
        $command = new TemplateCommand();
        $args = new Arguments([], ['prefix' => 'Admin'], []);
        $io = $this->createMock(ConsoleIo::class);

        $result = $command->getContent($args, $io, 'view', $vars);
        $this->assertSameAsFile(__FUNCTION__ . '-view.php', $result);

        $result = $command->getContent($args, $io, 'add', $vars);
        $this->assertSameAsFile(__FUNCTION__ . '-add.php', $result);
    }

    /**
     * test Bake method
     *
     * @return void
     */
    public function testBakeView()
    {
        $this->generatedFile = ROOT . 'templates/Authors/view.php';
        $this->exec('bake template authors view');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Test generating view template with hidden fields
     *
     * @return void
     */
    public function testBakeViewHiddenFields()
    {
        $this->generatedFile = ROOT . 'templates/HiddenFields/view.php';
        $this->exec('bake template HiddenFields view');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an edit file
     *
     * @return void
     */
    public function testBakeEdit()
    {
        $this->generatedFile = ROOT . 'templates/Authors/edit.php';
        $this->exec('bake template authors edit');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an edit file with a BelongsToMany association
     *
     * @return void
     */
    public function testBakeEditWithBelongsToManyAssociation()
    {
        $this->generatedFile = ROOT . 'templates/Articles/edit.php';
        $this->exec('bake template articles edit');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an index
     *
     * @return void
     */
    public function testBakeIndex()
    {
        $this->generatedFile = ROOT . 'templates/TemplateTaskComments/index.php';
        $this->exec('bake template template_task_comments index');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Test generating index template with hidden fields
     *
     * @return void
     */
    public function testBakeIndexHiddenFields()
    {
        $this->generatedFile = ROOT . 'templates/HiddenFields/index.php';
        $this->exec('bake template HiddenFields index');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test bake template with index limit overwrite
     *
     * @return void
     */
    public function testBakeIndexWithIndexLimit()
    {
        $this->generatedFile = ROOT . 'templates/TemplateTaskComments/index.php';
        $this->exec('bake template template_task_comments --index-columns 3 index');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test Bake with plugins
     *
     * @return void
     */
    public function testBakeIndexPlugin()
    {
        $this->_loadTestPlugin('BakeTest');
        $path = Plugin::templatePath('BakeTest');

        // Setup association to ensure properties don't have dots
        $model = $this->getTableLocator()->get('BakeTest.Comments');
        $model->belongsTo('Articles');

        $this->generatedFile = $path . 'Comments/index.php';
        $this->exec('bake template BakeTest.comments index');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
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
        $this->generatedFiles = [
            APP . '../templates/CategoryThreads/add.php',
            APP . '../templates/CategoryThreads/index.php',
        ];
        $this->exec('bake template CategoryThreads index');
        $this->assertExitCode(CommandInterface::CODE_SUCCESS);

        $this->exec('bake template CategoryThreads add');
        $this->assertExitCode(CommandInterface::CODE_SUCCESS);

        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileNotContains('rght', $this->generatedFiles[0]);
        $this->assertFileNotContains('lft', $this->generatedFiles[0]);

        $this->assertFileNotContains('rght', $this->generatedFiles[1]);
        $this->assertFileNotContains('lft', $this->generatedFiles[1]);
    }

    /**
     * Ensure that models associated with themselves do not have action
     * links generated.
     *
     * @return void
     */
    public function testBakeSelfAssociationsNoNavLinks()
    {
        $this->generatedFiles = [
            APP . '../templates/CategoryThreads/index.php',
        ];
        $this->exec('bake template CategoryThreads index');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileNotContains('New Parent Category', $this->generatedFiles[0]);
        $this->assertFileNotContains('List Parent Category', $this->generatedFiles[0]);
    }

    /**
     * Ensure that models associated with themselves do not have action
     * links generated.
     *
     * @return void
     */
    public function testBakeSelfAssociationsRelatedAssociations()
    {
        $this->generatedFile = ROOT . 'templates/CategoryThreads/view.php';
        $this->exec('bake template category_threads view');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);

        $this->assertFileContains('Related Category Threads', $this->generatedFile);
        $this->assertFileContains('Parent Category Thread', $this->generatedFile);
    }

    /**
     * test that baking a view with no template doesn't make a file.
     *
     * @return void
     */
    public function testBakeWithNoTemplate()
    {
        $this->expectException(MissingTemplateException::class);
        $this->expectExceptionMessage('No bake template found for "Bake.Template/delete"');
        $this->exec('bake template template_task_comments delete');
    }

    /**
     * Test execute no args.
     *
     * @return void
     */
    public function testMainNoArgs()
    {
        $this->exec('bake template');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertOutputContains('Possible tables to bake view templates for based on your current database:');
        $this->assertOutputContains('- Comments');
        $this->assertOutputContains('- Articles');
    }

    /**
     * test `cake bake view $controller view`
     *
     * @return void
     */
    public function testMainWithActionParam()
    {
        $this->generatedFile = ROOT . 'templates/TemplateTaskComments/view.php';
        $this->exec('bake template TemplateTaskComments view');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertFileDoesNotExist(
            ROOT . 'templates/TemplateTaskComments/edit.php',
            'no extra files'
        );
        $this->assertFileDoesNotExist(
            ROOT . 'templates/TemplateTaskComments/add.php',
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
            ROOT . 'templates/TemplateTaskComments/index.php',
            ROOT . 'templates/TemplateTaskComments/add.php',
        ];
        $this->exec('bake template TemplateTaskComments');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileDoesNotExist(
            ROOT . 'templates/TemplateTaskComments/edit.php',
            'no extra files'
        );
        $this->assertFileDoesNotExist(
            ROOT . 'templates/TemplateTaskComments/view.php',
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
        $path = Plugin::templatePath('TestBake');

        $this->generatedFile = $path . 'Comments/index.php';
        $this->exec('bake template --connection test TestBake.Comments index');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertFileDoesNotExist(
            $path . 'Comments/view.php',
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
            ROOT . 'templates/Blog/index.php',
            ROOT . 'templates/Blog/view.php',
            ROOT . 'templates/Blog/add.php',
            ROOT . 'templates/Blog/edit.php',
        ];
        $this->exec('bake template --controller Blog Posts');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertOutputNotContains('No bake template found');
        $this->assertErrorEmpty();
    }

    /**
     * test `cake bake view $controller --prefix Admin`
     *
     * @return void
     */
    public function testMainWithControllerAndAdminFlag()
    {
        $this->generatedFiles = [
            ROOT . 'templates/Admin/Posts/index.php',
            ROOT . 'templates/Admin/Posts/add.php',
        ];
        $this->exec('bake template --prefix Admin Posts');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
    }

    /**
     * test `cake bake view posts index list`
     *
     * @return void
     */
    public function testMainWithAlternateTemplates()
    {
        $this->generatedFile = ROOT . 'templates/TemplateTaskComments/list.php';
        $this->exec('bake template TemplateTaskComments index list');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertFileContains('Template Task Comments', $this->generatedFile);
    }

    /**
     * test `cake bake template MissingTableClass`
     *
     * @return void
     */
    public function testMainWithMissingTable()
    {
        $this->exec('bake template MissingTableClass');

        $this->assertExitCode(CommandInterface::CODE_ERROR);
    }
}
