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

use Bake\Command\ModelCommand;
use Bake\Test\TestCase\TestCase;
use Cake\Console\Arguments;
use Cake\Console\CommandInterface;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlite;
use Cake\Database\Driver\Sqlserver;
use Cake\Datasource\ConnectionManager;

/**
 * ModelCommand test class
 */
class ModelCommandTest extends TestCase
{
    /**
     * fixtures
     *
     * Don't sort this list alphabetically - otherwise there are table constraints
     * which fail when using postgres
     *
     * @var array<string>
     */
    protected $fixtures = [
        'plugin.Bake.Comments',
        'plugin.Bake.Tags',
        'plugin.Bake.ArticlesTags',
        'plugin.Bake.BakeArticles',
        'plugin.Bake.TodoTasks',
        'plugin.Bake.TodoItems',
        'plugin.Bake.TodoLabels',
        'plugin.Bake.TodoItemsTodoLabels',
        'plugin.Bake.CategoryThreads',
        'plugin.Bake.Invitations',
        'plugin.Bake.NumberTrees',
        'plugin.Bake.Users',
        'plugin.Bake.UniqueFields',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Model' . DS;
        $this->setAppNamespace('Bake\Test\App');
        $this->useCommandRunner();

        $this->getTableLocator()->clear();
    }

    /**
     * teardown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->getTableLocator()->clear();
    }

    /**
     * Test that listAll uses the connection property
     *
     * @return void
     */
    public function testListAllConnection()
    {
        $this->exec('bake model --connection test');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertOutputContains('- TodoItems');
        $this->assertOutputContains('- TodoTasks');
        $this->assertOutputContains('- TodoLabels');
        $this->assertOutputContains('- TodoItemsTodoLabels');
        $this->assertOutputContains('- CategoryThreads');
    }

    /**
     * Test getNameName() method.
     *
     * @return void
     */
    public function testGetTable()
    {
        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getTable('TodoItems', $args);
        $this->assertSame('todo_items', $result);

        $args = new Arguments([], ['table' => 'items'], []);
        $result = $command->getTable('TodoItems', $args);
        $this->assertSame('items', $result);
    }

    /**
     * Test getting the a table class.
     *
     * @return void
     */
    public function testGetTableObject()
    {
        $command = new ModelCommand();
        $command->connection = 'test';

        $result = $command->getTableObject('TodoItems', 'todo_items');
        $this->assertInstanceOf('Cake\ORM\Table', $result);
        $this->assertSame('todo_items', $result->getTable());
        $this->assertSame('TodoItems', $result->getAlias());

        $command->plugin = 'BakeTest';
        $result = $command->getTableObject('Authors', 'todo_items');
        $this->assertInstanceOf('BakeTest\Model\Table\AuthorsTable', $result);
    }

    /**
     * Test getting the a table class with a table prefix.
     *
     * @return void
     */
    public function testGetTableObjectPrefix()
    {
        $command = new ModelCommand();
        $command->connection = 'test';
        $command->tablePrefix = 'my_prefix_';

        $result = $command->getTableObject('TodoItems', 'todo_items');
        $this->assertSame('my_prefix_todo_items', $result->getTable());
        $this->assertInstanceOf('Cake\ORM\Table', $result);
        $this->assertSame('TodoItems', $result->getAlias());

        $command->plugin = 'BakeTest';
        $result = $command->getTableObject('Authors', 'todo_items');
        $this->assertSame('my_prefix_todo_items', $result->getTable());
        $this->assertInstanceOf('BakeTest\Model\Table\AuthorsTable', $result);
    }

    /**
     * Tests validating supported table and column names.
     */
    public function testValidateNamesWithValid(): void
    {
        $command = new ModelCommand();
        $command->connection = 'test';

        $schema = $command->getTableObject('TodoItems', 'todo_items')->getSchema();
        $schema->addColumn('_valid', ['type' => 'string', 'length' => null]);

        $io = $this->createMock(ConsoleIo::class);
        $io->expects($this->never())->method('abort');
        $command->validateNames($schema, $io);
    }

    /**
     * Tests validating supported table and column names.
     */
    public function testValidateNamesWithInvalid(): void
    {
        $command = new ModelCommand();
        $command->connection = 'test';

        $schema = $command->getTableObject('TodoItems', 'todo_items')->getSchema();
        $schema->addColumn('0invalid', ['type' => 'string', 'length' => null]);

        $io = $this->createMock(ConsoleIo::class);
        $io->expects($this->once())->method('abort');
        $command->validateNames($schema, $io);
    }

    /**
     * Test applying associations.
     *
     * @return void
     */
    public function testApplyAssociations()
    {
        $articles = $this->getTableLocator()->get('TodoItems');
        $assocs = [
            'belongsTo' => [
                [
                    'alias' => 'Users',
                    'foreignKey' => 'user_id',
                ],
            ],
            'hasMany' => [
                [
                    'alias' => 'TodoTasks',
                    'foreignKey' => 'todo_item_id',
                ],
            ],
            'belongsToMany' => [
                [
                    'alias' => 'TodoLabels',
                    'foreignKey' => 'todo_item_id',
                    'joinTable' => 'todo_items_todo_labels',
                    'targetForeignKey' => 'todo_label_id',
                ],
            ],
        ];
        $original = $articles->associations()->keys();
        $this->assertEquals([], $original);

        $command = new ModelCommand();
        $command->connection = 'test';

        $command->applyAssociations($articles, $assocs);
        $new = $articles->associations()->keys();
        $expected = ['Users', 'TodoTasks', 'TodoLabels'];
        $this->assertEquals($expected, $new);
    }

    /**
     * Test applying associations does nothing on a concrete class
     *
     * @return void
     */
    public function testApplyAssociationsConcreteClass()
    {
        Configure::write('App.namespace', 'Bake\Test\App');
        $articles = $this->getTableLocator()->get('Articles');
        $assocs = [
            'belongsTo' => [
                [
                    'alias' => 'BakeUsers',
                    'foreignKey' => 'bake_user_id',
                ],
            ],
            'hasMany' => [
                [
                    'alias' => 'BakeComments',
                    'foreignKey' => 'bake_article_id',
                ],
            ],
            'belongsToMany' => [
                [
                    'alias' => 'BakeTags',
                    'foreignKey' => 'bake_article_id',
                    'joinTable' => 'bake_articles_bake_tags',
                    'targetForeignKey' => 'bake_tag_id',
                ],
            ],
        ];
        $original = $articles->associations()->keys();

        $command = new ModelCommand();
        $command->applyAssociations($articles, $assocs);
        $new = $articles->associations()->keys();
        $this->assertEquals($original, $new);
    }

    /**
     * Test getAssociations
     *
     * @return void
     */
    public function testGetAssociations()
    {
        $items = $this->getTableLocator()->get('TodoItems');

        $command = new ModelCommand();
        $command->connection = 'test';

        $args = new Arguments([], [], []);
        $io = $this->createMock(ConsoleIo::class);
        $result = $command->getAssociations($items, $args, $io);

        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'Users',
                    'foreignKey' => 'user_id',
                    'joinType' => 'INNER',
                ],
            ],
            'hasOne' => [
                [
                    'alias' => 'TodoReminders',
                    'foreignKey' => 'todo_item_id',
                ],
            ],
            'hasMany' => [
                [
                    'alias' => 'TodoTasks',
                    'foreignKey' => 'todo_item_id',
                ],
            ],
            'belongsToMany' => [
                [
                    'alias' => 'TodoLabels',
                    'foreignKey' => 'todo_item_id',
                    'joinTable' => 'todo_items_todo_labels',
                    'targetForeignKey' => 'todo_label_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getAssociations with off flag.
     *
     * @return void
     */
    public function testGetAssociationsNoFlag()
    {
        $command = new ModelCommand();
        $command->connection = 'test';

        $arguments = new Arguments([], ['no-associations' => true], []);
        $io = $this->createMock(ConsoleIo::class);
        $articles = $this->getTableLocator()->get('BakeArticle');
        $this->assertEquals([], $command->getAssociations($articles, $arguments, $io));
    }

    /**
     * Test getAssociations in a plugin
     *
     * @return void
     */
    public function testGetAssociationsPlugin()
    {
        $items = $this->getTableLocator()->get('TodoItems');
        $command = new ModelCommand();
        $command->plugin = 'TestBake';
        $command->connection = 'test';

        $args = new Arguments([], [], []);
        $io = $this->createMock(ConsoleIo::class);
        $result = $command->getAssociations($items, $args, $io);
        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'Users',
                    'className' => 'TestBake.Users',
                    'foreignKey' => 'user_id',
                    'joinType' => 'INNER',
                ],
            ],
            'hasOne' => [
                [
                    'alias' => 'TodoReminders',
                    'className' => 'TestBake.TodoReminders',
                    'foreignKey' => 'todo_item_id',
                ],
            ],
            'hasMany' => [
                [
                    'alias' => 'TodoTasks',
                    'className' => 'TestBake.TodoTasks',
                    'foreignKey' => 'todo_item_id',
                ],
            ],
            'belongsToMany' => [
                [
                    'alias' => 'TodoLabels',
                    'className' => 'TestBake.TodoLabels',
                    'foreignKey' => 'todo_item_id',
                    'joinTable' => 'todo_items_todo_labels',
                    'targetForeignKey' => 'todo_label_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that association generation ignores `anything_id` fields if
     * AnythingsTable object nor `anythings` database table exist
     *
     * @return void
     */
    public function testGetAssociationsIgnoreUnderscoreIdIfNoDbTable()
    {
        $items = $this->getTableLocator()->get('TodoItems');

        $items->setSchema($items->getSchema()->addColumn('anything_id', ['type' => 'integer']));
        $command = new ModelCommand();
        $command->connection = 'test';

        $args = new Arguments([], [], []);
        $io = $this->createMock(ConsoleIo::class);
        $result = $command->getAssociations($items, $args, $io);
        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'Users',
                    'foreignKey' => 'user_id',
                    'joinType' => 'INNER',
                ],
            ],
            'hasMany' => [
                [
                    'alias' => 'TodoTasks',
                    'foreignKey' => 'todo_item_id',
                ],
            ],
            'belongsToMany' => [
                [
                    'alias' => 'TodoLabels',
                    'foreignKey' => 'todo_item_id',
                    'joinTable' => 'todo_items_todo_labels',
                    'targetForeignKey' => 'todo_label_id',
                ],
            ],
            'hasOne' => [
                [
                    'alias' => 'TodoReminders',
                    'foreignKey' => 'todo_item_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that association generation adds association when `anything_id` fields and
     * AnythingsTable object exist even if no db table
     *
     * @return void
     */
    public function testGetAssociationsAddAssociationIfTableExist()
    {
        $items = $this->getTableLocator()->get('TodoItems');

        $items->setSchema($items->getSchema()->addColumn('template_task_comment_id', ['type' => 'integer']));
        $command = new ModelCommand();
        $command->connection = 'test';

        $args = new Arguments([], [], []);
        $io = $this->createMock(ConsoleIo::class);
        $result = $command->getAssociations($items, $args, $io);
        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'Users',
                    'foreignKey' => 'user_id',
                    'joinType' => 'INNER',
                ],
                [
                    'alias' => 'TemplateTaskComments',
                    'foreignKey' => 'template_task_comment_id',
                ],
            ],
            'hasMany' => [
                [
                    'alias' => 'TodoTasks',
                    'foreignKey' => 'todo_item_id',
                ],
            ],
            'belongsToMany' => [
                [
                    'alias' => 'TodoLabels',
                    'foreignKey' => 'todo_item_id',
                    'joinTable' => 'todo_items_todo_labels',
                    'targetForeignKey' => 'todo_label_id',
                ],
            ],
            'hasOne' => [
                [
                    'alias' => 'TodoReminders',
                    'foreignKey' => 'todo_item_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that association generation adds `Anythings` association for `anything_id` field
     * when using `--skip-relation-check` option, even if no db table exists
     *
     * @return void
     */
    public function testGetAssociationsAddAssociationIfNoTableExistButAliasIsAllowed()
    {
        $items = $this->getTableLocator()->get('TodoItems');

        $items->setSchema($items->getSchema()->addColumn('anything_id', ['type' => 'integer']));
        $command = new ModelCommand();
        $command->connection = 'test';

        $args = new Arguments([], ['skip-relation-check' => true], []);
        $io = $this->createMock(ConsoleIo::class);
        $result = $command->getAssociations($items, $args, $io);
        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'Users',
                    'foreignKey' => 'user_id',
                    'joinType' => 'INNER',
                ],
                [
                    'alias' => 'Anythings',
                    'foreignKey' => 'anything_id',
                ],
            ],
            'hasMany' => [
                [
                    'alias' => 'TodoTasks',
                    'foreignKey' => 'todo_item_id',
                ],
            ],
            'belongsToMany' => [
                [
                    'alias' => 'TodoLabels',
                    'foreignKey' => 'todo_item_id',
                    'joinTable' => 'todo_items_todo_labels',
                    'targetForeignKey' => 'todo_label_id',
                ],
            ],
            'hasOne' => [
                [
                    'alias' => 'TodoReminders',
                    'foreignKey' => 'todo_item_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that association generation ignores `_id` fields
     *
     * @return void
     */
    public function testGetAssociationsIgnoreUnderscoreId()
    {
        $model = $this->getTableLocator()->get('BakeComments');
        $model->setSchema([
            'id' => ['type' => 'integer'],
            '_id' => ['type' => 'integer'],
        ]);

        $args = new Arguments([], [], []);
        $io = $this->createMock(ConsoleIo::class);

        $command = new ModelCommand();
        $command->connection = 'test';
        $result = $command->getAssociations($model, $args, $io);
        $expected = [
            'hasOne' => [],
            'hasMany' => [],
            'belongsTo' => [],
            'belongsToMany' => [],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * test finding referenced tables using constraints.
     *
     * @return void
     */
    public function testGetAssociationsConstraints()
    {
        $model = $this->getTableLocator()->get('Invitations');
        $command = new ModelCommand();
        $command->connection = 'test';

        $args = new Arguments([], [], []);
        $io = $this->createMock(ConsoleIo::class);
        $result = $command->getAssociations($model, $args, $io);

        $expected = [
            [
                'alias' => 'Users',
                'foreignKey' => 'sender_id',
                'joinType' => 'INNER',
            ],
            [
                'alias' => 'Users',
                'foreignKey' => 'receiver_id',
                'joinType' => 'INNER',
            ],
        ];
        $this->assertEquals($expected, $result['belongsTo']);
    }

    /**
     * test that belongsTo generation works.
     *
     * @return void
     */
    public function testBelongsToGeneration()
    {
        $model = $this->getTableLocator()->get('TodoItems');
        $command = new ModelCommand();
        $command->connection = 'test';

        $result = $command->findBelongsTo($model, []);
        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'Users',
                    'foreignKey' => 'user_id',
                    'joinType' => 'INNER',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);

        $model = $this->getTableLocator()->get('CategoryThreads');
        $result = $command->findBelongsTo($model, []);
        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'ParentCategoryThreads',
                    'className' => 'CategoryThreads',
                    'foreignKey' => 'parent_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);

        $command->plugin = 'Blog';
        $result = $command->findBelongsTo($model, []);
        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'ParentCategoryThreads',
                    'className' => 'Blog.CategoryThreads',
                    'foreignKey' => 'parent_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that belongsTo association generation uses constraints on the table
     *
     * @return void
     */
    public function testBelongsToGenerationConstraints()
    {
        $model = $this->getTableLocator()->get('Invitations');
        $command = new ModelCommand();
        $command->connection = 'test';
        $result = $command->findBelongsTo($model, []);
        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'Users',
                    'foreignKey' => 'sender_id',
                    'joinType' => 'INNER',
                ],
                [
                    'alias' => 'Users',
                    'foreignKey' => 'receiver_id',
                    'joinType' => 'INNER',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that belongsTo generation works for models with composite
     * primary keys
     *
     * @return void
     */
    public function testBelongsToGenerationCompositeKey()
    {
        $model = $this->getTableLocator()->get('TodoItemsTodoLabels');
        $command = new ModelCommand();
        $command->connection = 'test';
        $result = $command->findBelongsTo($model, []);
        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'TodoItems',
                    'foreignKey' => 'todo_item_id',
                    'joinType' => 'INNER',
                ],
                [
                    'alias' => 'TodoLabels',
                    'foreignKey' => 'todo_label_id',
                    'joinType' => 'INNER',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that belongsTo generation ignores _id mid-column
     *
     * @return void
     */
    public function testBelongsToGenerationIdMidColumn()
    {
        $model = $this->getTableLocator()->get('TodoItems');
        $model->setSchema([
            'id' => ['type' => 'integer'],
            'thing_id_field' => ['type' => 'integer'],
        ]);
        $command = new ModelCommand();
        $result = $command->findBelongsTo($model, []);
        $this->assertEquals([], $result);
    }

    /**
     * Test that belongsTo generation ignores primary key fields
     *
     * @return void
     */
    public function testBelongsToGenerationPrimaryKey()
    {
        $model = $this->getTableLocator()->get('Articles');
        $model->setSchema([
            'usr_id' => ['type' => 'integer'],
            'name' => ['type' => 'string'],
            '_constraints' => [
                'primary' => ['type' => 'primary', 'columns' => ['usr_id']],
            ],
        ]);
        $command = new ModelCommand();
        $result = $command->findBelongsTo($model, []);
        $this->assertEquals([], $result);
    }

    /**
     * test that hasOne relations are generated properly.
     *
     * @return void
     */
    public function testHasOneGeneration()
    {
        $command = new ModelCommand();
        $command->connection = 'test';

        $model = $this->getTableLocator()->get('TodoItems');
        $result = $command->findHasOne($model, []);
        $expected = [
            'hasOne' => [
                [
                    'alias' => 'TodoReminders',
                    'foreignKey' => 'todo_item_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);

        $model = $this->getTableLocator()->get('SelfReferencingUniqueKeys');
        $result = $command->findHasOne($model, []);
        $this->assertEmpty($result);
    }

    /**
     * test that hasMany relations are generated properly.
     *
     * @return void
     */
    public function testHasManyGeneration()
    {
        $command = new ModelCommand();
        $command->connection = 'test';

        $model = $this->getTableLocator()->get('TodoItems');
        $result = $command->findHasMany($model, []);
        $expected = [
            'hasMany' => [
                [
                    'alias' => 'TodoTasks',
                    'foreignKey' => 'todo_item_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);

        $model = $this->getTableLocator()->get('CategoryThreads');
        $result = $command->findHasMany($model, []);
        $expected = [
            'hasMany' => [
                [
                    'alias' => 'ChildCategoryThreads',
                    'className' => 'CategoryThreads',
                    'foreignKey' => 'parent_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);

        $command->plugin = 'Blog';
        $result = $command->findHasMany($model, []);
        $expected = [
            'hasMany' => [
                [
                    'alias' => 'ChildCategoryThreads',
                    'className' => 'Blog.CategoryThreads',
                    'foreignKey' => 'parent_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);

        // self-reference foreign keys with a unique constraint will
        // generate hasMany instead of hasOne, until someone can come
        // up with a proper use case for the latter.
        $model = $this->getTableLocator()->get('SelfReferencingUniqueKeys');
        $command->plugin = null;
        $result = $command->findHasMany($model, []);
        $expected = [
            'hasMany' => [
                [
                    'alias' => 'ChildSelfReferencingUniqueKeys',
                    'className' => 'SelfReferencingUniqueKeys',
                    'foreignKey' => 'parent_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that HABTM generation works
     *
     * @return void
     */
    public function testHasAndBelongsToManyGeneration()
    {
        $command = new ModelCommand();
        $command->connection = 'test';
        $model = $this->getTableLocator()->get('TodoItems');
        $result = $command->findBelongsToMany($model, []);
        $expected = [
            'belongsToMany' => [
                [
                    'alias' => 'TodoLabels',
                    'foreignKey' => 'todo_item_id',
                    'joinTable' => 'todo_items_todo_labels',
                    'targetForeignKey' => 'todo_label_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getting the entity property schema.
     *
     * @return void
     */
    public function testGetEntityPropertySchema()
    {
        $model = $this->getTableLocator()->get('TodoItems');
        $model->belongsTo('BakeUsers');
        $model->hasMany('BakeTest.Authors');
        $model->getSchema()->setColumnType('created', 'timestamp');
        $model->getSchema()->setColumnType('updated', 'timestamp');

        $command = new ModelCommand();
        $result = $command->getEntityPropertySchema($model);
        $expected = [
            'id' => [
                'kind' => 'column',
                'type' => 'integer',
                'null' => false,
            ],
            'title' => [
                'kind' => 'column',
                'type' => 'string',
                'null' => false,
            ],
            'body' => [
                'kind' => 'column',
                'type' => 'text',
                'null' => true,
            ],
            'effort' => [
                'kind' => 'column',
                'type' => 'decimal',
                'null' => false,
            ],
            'completed' => [
                'kind' => 'column',
                'type' => 'boolean',
                'null' => false,
            ],
            'created' => [
                'kind' => 'column',
                'type' => 'timestamp',
                'null' => true,
            ],
            'updated' => [
                'kind' => 'column',
                'type' => 'timestamp',
                'null' => true,
            ],
        ];
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $result);

            $this->assertArrayHasKey('kind', $result[$key]);
            $this->assertSame($value['kind'], $result[$key]['kind']);

            $this->assertArrayHasKey('type', $result[$key]);
            $this->assertSame($value['type'], $result[$key]['type']);

            $this->assertArrayHasKey('null', $result[$key]);
            $this->assertSame($value['null'], $result[$key]['null']);
        }

        $expectedAssociations = [
            'bake_user' => [
                'kind' => 'association',
                'association' => $model->getAssociation('BakeUsers'),
                'type' => '\Bake\Test\App\Model\Entity\BakeUser',
            ],
            'authors' => [
                'kind' => 'association',
                'association' => $model->getAssociation('Authors'),
                'type' => '\BakeTest\Model\Entity\Author',
            ],
        ];
        foreach ($expectedAssociations as $key => $expected) {
            $this->assertEquals($expected, $result[$key]);
        }
    }

    /**
     * Test getting accessible fields.
     *
     * @return void
     */
    public function testGetFields()
    {
        $model = $this->getTableLocator()->get('TodoItems');

        $command = new ModelCommand();
        $result = $command->getFields($model, new Arguments([], [], []));
        $expected = [
            'user_id',
            'title',
            'body',
            'effort',
            'completed',
            'todo_task_count',
            'created',
            'updated',
        ];
        $this->assertSame($expected, $result);
    }

    /**
     * Test getting accessible fields with the no- option
     *
     * @return void
     */
    public function testGetFieldsDisabled()
    {
        $model = $this->getTableLocator()->get('TodoItems');
        $args = new Arguments([], ['no-fields' => true], []);
        $command = new ModelCommand();
        $result = $command->getFields($model, $args);
        $this->assertFalse($result);
    }

    /**
     * Test getting accessible fields with a whitelist
     *
     * @return void
     */
    public function testGetFieldsWhiteList()
    {
        $model = $this->getTableLocator()->get('TodoItems');

        $args = new Arguments([], ['fields' => 'id, title  , , body ,  created'], []);
        $command = new ModelCommand();
        $result = $command->getFields($model, $args);
        $expected = [
            'id',
            'title',
            'body',
            'created',
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getting hidden fields.
     *
     * @return void
     */
    public function testGetHiddenFields()
    {
        $model = $this->getTableLocator()->get('Users');

        $args = new Arguments([], [], []);
        $command = new ModelCommand();
        $result = $command->getHiddenFields($model, $args);
        $expected = [
            'password',
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getting hidden field with the no- option
     *
     * @return void
     */
    public function testGetHiddenFieldsDisabled()
    {
        $model = $this->getTableLocator()->get('Users');

        $args = new Arguments([], ['no-hidden' => true], []);
        $command = new ModelCommand();
        $result = $command->getHiddenFields($model, $args);
        $this->assertEquals([], $result);
    }

    /**
     * Test getting hidden field with a whitelist
     *
     * @return void
     */
    public function testGetHiddenFieldsWhiteList()
    {
        $model = $this->getTableLocator()->get('Users');

        $args = new Arguments([], ['hidden' => 'id, title  , , body ,  created'], []);
        $command = new ModelCommand();
        $result = $command->getHiddenFields($model, $args);
        $expected = [
            'id',
            'title',
            'body',
            'created',
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getting primary key
     *
     * @return void
     */
    public function testGetPrimaryKey()
    {
        $model = $this->getTableLocator()->get('TodoItems');
        $command = new ModelCommand();
        $args = new Arguments([], [], []);

        $result = $command->getPrimaryKey($model, $args);
        $expected = ['id'];
        $this->assertEquals($expected, $result);

        $args = new Arguments([], ['primary-key' => 'id, , account_id'], []);
        $result = $command->getPrimaryKey($model, $args);
        $expected = ['id', 'account_id'];
        $this->assertEquals($expected, $result);
    }

    /**
     * test getting validation rules with the no-validation rule.
     *
     * @return void
     */
    public function testGetValidationDisabled()
    {
        $model = $this->getTableLocator()->get('TodoItems');
        $command = new ModelCommand();
        $args = new Arguments([], ['no-validation' => true], []);
        $result = $command->getValidation($model, [], $args);
        $this->assertEquals([], $result);
    }

    /**
     * test getting validation rules.
     *
     * @return void
     */
    public function testGetValidation()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Postgres, 'Incompatible with postgres');
        $this->skipIf($driver instanceof Sqlserver, 'Incompatible with sqlserver');

        $model = $this->getTableLocator()->get('TodoItems');

        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getValidation($model, [], $args);
        $expected = [
            'user_id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ['create']],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'title' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ['create']],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
                'maxLength' => ['rule' => 'maxLength', 'args' => [50]],
            ],
            'body' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => []],
            ],
            'effort' => [
                'decimal' => ['rule' => 'decimal', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'completed' => [
                'boolean' => ['rule' => 'boolean', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'todo_task_count' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * test getting validation rules.
     *
     * @return void
     */
    public function testGetValidationSigned()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Sqlite, 'Incompatible with sqlite');
        $this->skipIf($driver instanceof Mysql, 'Incompatible with mysql');

        $model = $this->getTableLocator()->get('TodoTasks');
        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getValidation($model, [], $args);
        $expected = [
            'todo_item_id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ['create']],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'title' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ['create']],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
                'maxLength' => ['rule' => 'maxLength', 'args' => [50]],
            ],
            'body' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => []],
            ],
            'effort' => [
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
                'decimal' => ['rule' => 'decimal', 'args' => []],
            ],
            'completed' => [
                'boolean' => ['rule' => 'boolean', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getting validation rules for unique date time columns
     *
     * @return void
     */
    public function testGetValidationUniqueDateField()
    {
        $model = $this->getTableLocator()->get('TodoItems');
        $schema = $model->getSchema();
        $schema
            ->addColumn('release_date', ['type' => 'datetime'])
            ->addConstraint('unique_date', [
                'columns' => ['release_date'],
                'type' => 'unique',
            ]);
        $command = new ModelCommand();
        $args = new Arguments([], [], []);

        $result = $command->getValidation($model, [], $args);
        $this->assertArrayHasKey('release_date', $result);
        $expected = [
            'dateTime' => ['rule' => 'dateTime', 'args' => []],
            'requirePresence' => ['rule' => 'requirePresence', 'args' => ['create']],
            'notEmpty' => ['rule' => 'notEmptyDateTime', 'args' => []],
        ];
        $this->assertEquals($expected, $result['release_date']);
    }

    /**
     * test getting validation rules for tree-ish models
     *
     * @return void
     */
    public function testGetValidationTree()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Postgres, 'Incompatible with postgres');
        $this->skipIf($driver instanceof Sqlserver, 'Incompatible with sqlserver');

        $model = $this->getTableLocator()->get('NumberTrees');
        $args = new Arguments([], [], []);
        $command = new ModelCommand();
        $result = $command->getValidation($model, [], $args);
        $expected = [
            'name' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ['create']],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
                'maxLength' => ['rule' => 'maxLength', 'args' => [50]],
            ],
            'parent_id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => []],
            ],
            'depth' => [
                'nonNegativeInteger' => ['rule' => 'nonNegativeInteger', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => []],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * test getting validation rules for tree-ish models
     *
     * @return void
     */
    public function testGetValidationTreeSigned()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Sqlite, 'Incompatible with sqlite');
        $this->skipIf($driver instanceof Mysql, 'Incompatible with mysql');

        $model = $this->getTableLocator()->get('NumberTrees');
        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getValidation($model, [], $args);
        $expected = [
            'name' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ['create']],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
                'maxLength' => ['rule' => 'maxLength', 'args' => [50]],
            ],
            'parent_id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => []],
            ],
            'depth' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => []],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * test getting validation rules and exempting foreign keys
     *
     * @return void
     */
    public function testGetValidationExcludeForeignKeysSigned()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Sqlite, 'Incompatible with sqlite');
        $this->skipIf($driver instanceof Mysql, 'Incompatible with mysql');

        $model = $this->getTableLocator()->get('TodoItems');
        $associations = [
            'belongsTo' => [
                'Users' => ['foreignKey' => 'user_id'],
            ],
        ];
        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getValidation($model, $associations, $args);
        $expected = [
            'title' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ['create']],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
                'maxLength' => ['rule' => 'maxLength', 'args' => [50]],
            ],
            'body' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => []],
            ],
            'effort' => [
                'decimal' => ['rule' => 'decimal', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'completed' => [
                'boolean' => ['rule' => 'boolean', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'todo_task_count' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'user_id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * test getting validation rules with the no-rules param.
     *
     * @return void
     */
    public function testGetRulesDisabled()
    {
        $model = $this->getTableLocator()->get('Users');
        $command = new ModelCommand();
        $args = new Arguments([], ['no-rules' => true], []);
        $result = $command->getRules($model, [], $args);
        $this->assertEquals([], $result);
    }

    /**
     * Tests the getRules method
     *
     * @return void
     */
    public function testGetRules()
    {
        $model = $this->getTableLocator()->get('Users');
        $associations = [
            'belongsTo' => [
                [
                    'alias' => 'Countries',
                    'foreignKey' => 'country_id',
                ],
                [
                    'alias' => 'Sites',
                    'foreignKey' => 'site_id',
                ],
            ],
            'hasMany' => [
                [
                    'alias' => 'BakeComments',
                    'foreignKey' => 'bake_user_id',
                ],
            ],
        ];
        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getRules($model, $associations, $args);
        $expected = [
            'username' => [
                'name' => 'isUnique',
                'fields' => ['username'],
                'options' => [],
            ],
            'country_id' => [
                'name' => 'existsIn',
                'extra' => 'Countries',
                'options' => [],
            ],
            'site_id' => [
                'name' => 'existsIn',
                'extra' => 'Sites',
                'options' => [],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the getRules with unique keys.
     *
     * Multi-column constraints are ignored as they would
     * require a break in compatibility.
     *
     * @return void
     */
    public function testGetRulesUniqueKeys()
    {
        $model = $this->getTableLocator()->get('TodoItems');
        $model->getSchema()->addConstraint('unique_title', [
            'type' => 'unique',
            'columns' => ['title'],
        ]);
        $model->getSchema()->addConstraint('ignored_constraint', [
            'type' => 'unique',
            'columns' => ['title', 'user_id'],
        ]);

        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getRules($model, [], $args);
        $expected = [
            'title' => [
                'name' => 'isUnique',
                'fields' => ['title', 'user_id'],
                'options' => [],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that specific behaviors are auto-detected
     *
     * @return void
     */
    public function testGetBehaviorsAutoDetect()
    {
        $command = new ModelCommand();
        $command->connection = 'test';

        $model = $this->getTableLocator()->get('NumberTrees');
        $result = $command->getBehaviors($model);
        $this->assertEquals(['Tree' => []], $result);

        $model = $this->getTableLocator()->get('TodoItems');
        $result = $command->getBehaviors($model);
        $this->assertEquals(['Timestamp' => []], $result);
    }

    /**
     * Test getDisplayField() method.
     *
     * @return void
     */
    public function testGetDisplayField()
    {
        $model = $this->getTableLocator()->get('TodoItems');
        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getDisplayField($model, $args);
        $this->assertSame('title', $result);

        $args = new Arguments([], ['display-field' => 'custom'], []);
        $result = $command->getDisplayField($model, $args);
        $this->assertSame('custom', $result);
    }

    /**
     * Ensure that the fixture object is correctly called.
     *
     * @return void
     */
    public function testBakeFixture()
    {
        $this->generatedFiles = [
            APP . 'Model/Table/TodoItemsTable.php',
            APP . 'Model/Entity/TodoItem.php',
            ROOT . 'tests/Fixture/TodoItemsFixture.php',
        ];
        $this->exec('bake model --no-test todo_items');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileDoesNotExist(ROOT . 'tests/TestCase/Model/Table/TodoItemsTableTest.php');
    }

    /**
     * Ensure that the fixture baking can be disabled
     *
     * @return void
     */
    public function testBakeFixtureDisabled()
    {
        $this->generatedFiles = [
            APP . 'Model/Table/TodoItemsTable.php',
            APP . 'Model/Entity/TodoItem.php',
        ];
        $this->exec('bake model --no-test --no-fixture todo_items');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileDoesNotExist(ROOT . 'tests/Fixture/TodoItemsFixture.php');
        $this->assertFileDoesNotExist(ROOT . 'tests/TestCase/Model/Table/TodoItemsTableTest.php');
    }

    /**
     * Ensure that the test object is correctly called.
     *
     * @return void
     */
    public function testBakeTest()
    {
        $this->generatedFiles = [
            APP . 'Model/Table/TodoItemsTable.php',
            APP . 'Model/Entity/TodoItem.php',
            ROOT . 'tests/TestCase/Model/Table/TodoItemsTableTest.php',
        ];
        $this->exec('bake model --no-fixture todo_items');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileDoesNotExist(ROOT . 'tests/Fixture/TodoItemsFixture.php');
    }

    /**
     * test baking validation
     *
     * @return void
     */
    public function testBakeTableValidation()
    {
        $this->generatedFiles = [
            APP . 'Model/Table/TestBakeArticlesTable.php',
        ];

        $validation = [
            'id' => [
                'valid' => [
                    'rule' => 'numeric',
                    'args' => [],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => ['create'],
                ],
            ],
            'name' => [
                'valid' => [
                    'rule' => 'scalar',
                    'args' => [],
                ],
                'maxLength' => [
                    'rule' => 'maxLength',
                    'args' => [
                        100,
                        'Name must be shorter than 100 characters.',
                    ],
                ],
                'requirePresense' => [
                    'rule' => 'requirePresence',
                    'args' => ['create'],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => [null, false],
                ],
            ],
            'count' => [
                'valid' => [
                    'rule' => 'nonNegativeInteger',
                    'args' => [],
                ],
                'requirePresense' => [
                    'rule' => 'requirePresence',
                    'args' => ['create'],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => [null, false],
                ],
            ],
            'price' => [
                'valid' => [
                    'rule' => 'greaterThanOrEqual',
                    'args' => [
                        0,
                    ],
                ],
                'requirePresense' => [
                    'rule' => 'requirePresence',
                    'args' => ['create'],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => [null, false],
                ],
            ],
            'email' => [
                'valid' => [
                    'rule' => 'email',
                    'args' => [],
                ],
                'unique' => [
                    'rule' => 'validateUnique',
                    'provider' => 'table',
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => [],
                ],
            ],
            'image' => [
                'uploadedFile' => [
                    'rule' => 'uploadedFile',
                    'args' => [
                        [
                            'optional' => true,
                            'types' => ['image/jpeg'],
                        ],
                    ],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyFile',
                    'args' => [],
                ],
            ],
        ];

        $command = new ModelCommand();
        $command->connection = 'test';

        $name = 'TestBakeArticles';
        $args = new Arguments([$name], ['table' => 'bake_articles', 'force' => true], []);
        $io = new ConsoleIo($this->_out, $this->_err, $this->_in);

        $table = $command->getTable($name, $args);
        $tableObject = $command->getTableObject($name, $table);
        $data = $command->getTableContext($tableObject, $table, $name, $args, $io);
        $data['validation'] = $validation;
        $data['associations'] = [
            'belongsTo' => [],
            'hasMany' => [],
            'belongsToMany' => [],
        ];
        $data['rulesChecker'] = [];
        $command->bakeTable($tableObject, $data, $args, $io);

        $result = file_get_contents($this->generatedFiles[0]);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Tests generating table with rules checker.
     */
    public function testBakeTableRules(): void
    {
        $this->generatedFiles = [
            APP . 'Model/Table/UniqueFieldsTable.php',
        ];

        $command = new ModelCommand();
        $command->connection = 'test';

        $name = 'UniqueFields';
        $args = new Arguments([$name], ['table' => 'unique_fields', 'force' => true], []);
        $io = new ConsoleIo($this->_out, $this->_err, $this->_in);

        $table = $command->getTable($name, $args);
        $tableObject = $command->getTableObject($name, $table);
        $data = $command->getTableContext($tableObject, $table, $name, $args, $io);
        $data['validation'] = [];
        $command->bakeTable($tableObject, $data, $args, $io);

        $result = file_get_contents($this->generatedFiles[0]);
        $expected = file_get_contents($this->_compareBasePath . __FUNCTION__ . '.php');
        if (ConnectionManager::get('test')->getDriver() instanceof Sqlserver) {
            $expected = preg_replace("/'allowMultipleNulls' => true/", "'allowMultipleNulls' => false", $expected);
        }
        $this->assertTextEquals($expected, $result, 'Content does not match file ' . __FUNCTION__ . '.php');
    }

    /**
     * test baking with table config and connection option
     *
     * @return void
     */
    public function testBakeTableConfig()
    {
        $this->generatedFiles = [
            APP . 'Model/Table/ItemsTable.php',
            APP . 'Model/Entity/Item.php',
        ];
        $this->exec('bake model --no-test --no-fixture --connection test --table todo_items Items');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $result = file_get_contents($this->generatedFiles[0]);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an entity class
     *
     * @return void
     */
    public function testBakeEntitySimple()
    {
        $this->generatedFile = APP . 'Model/Entity/User.php';
        $this->exec('bake model --no-test --no-fixture --no-table --no-fields --no-hidden users');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Tests baking a file with no changes
     *
     * @return void
     */
    public function testBakeEntitySimpleUnchanged(): void
    {
        $this->generatedFile = APP . 'Model/Entity/User.php';
        $result = file_get_contents($this->_compareBasePath . __FUNCTION__ . '.php');
        file_put_contents($this->generatedFile, str_replace("\r\n", "\n", $result));

        $this->exec('bake model --no-test --no-fixture --no-table --no-fields --no-hidden users');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);

        $this->assertOutputContains(sprintf('Skipping update to `%s`. It already exists and would not change.', realpath($this->generatedFile)));
    }

    /**
     * test baking an entity class
     *
     * @return void
     */
    public function testBakeEntityFullContext()
    {
        $this->generatedFile = APP . 'Model/Entity/User.php';
        $this->exec('bake model --no-test --no-fixture --no-table users');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an entity with DocBlock property type hints.
     *
     * @return void
     */
    public function testBakeEntityWithPropertyTypeHints()
    {
        $model = $this->getTableLocator()->get('TodoItems');
        $model->belongsTo('Users');
        $model->hasMany('BakeTest.TodoTasks');
        $model->getSchema()->addColumn('array_type', [
            'type' => 'array',
        ]);
        $model->getSchema()->addColumn('json_type', [
            'type' => 'json',
        ]);
        $model->getSchema()->addColumn('unknown_type', [
            'type' => 'unknownType',
        ]);

        $this->generatedFile = APP . 'Model/Entity/TodoItem.php';
        $this->exec('bake model --no-test --no-fixture --no-table --no-fields todo_items');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an entity class
     *
     * @return void
     */
    public function testBakeEntityFieldsDefaults()
    {
        $this->generatedFiles = [
            APP . 'Model/Entity/TodoItem.php',
        ];
        $this->exec('bake model --no-test --no-table --no-fixture TodoItems');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $result = file_get_contents($this->generatedFiles[0]);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an entity class with no accessible fields.
     *
     * @return void
     */
    public function testBakeEntityNoFields()
    {
        $this->generatedFile = APP . 'Model/Entity/TodoItem.php';
        $this->exec('bake model --no-test --no-fixture --no-table --no-fields todo_items');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an entity class with a whitelist of accessible fields.
     *
     * @return void
     */
    public function testBakeEntityFieldsWhiteList()
    {
        $this->generatedFile = APP . 'Model/Entity/TodoItem.php';
        $this->exec('bake model --no-test --no-fixture --no-table --fields id,title,body,completed todo_items');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an entity class sets hidden fields.
     *
     * @return void
     */
    public function testBakeEntityHidden()
    {
        $this->generatedFile = APP . 'Model/Entity/User.php';
        $this->exec('bake model --no-test --no-fixture --no-table --no-fields --hidden password users');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an entity with non whitelisted hidden fields.
     *
     * @return void
     */
    public function testBakeEntityCustomHidden()
    {
        $this->generatedFile = APP . 'Model/Entity/User.php';
        $this->exec('bake model --no-test --no-fixture --no-table --no-fields --hidden foo,bar users');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test bake() with a -plugin param
     *
     * @return void
     */
    public function testBakeTableWithPlugin()
    {
        $this->_loadTestPlugin('BakeTest');
        $path = Plugin::path('BakeTest');

        $this->generatedFile = $path . 'src/Model/Table/UsersTable.php';

        $this->exec('bake model --no-validation --no-test --no-fixture --no-entity BakeTest.Users');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test generation with counter cach
     *
     * @return void
     */
    public function testBakeTableWithCounterCache()
    {
        $this->generatedFile = APP . 'Model/Table/TodoTasksTable.php';

        $this->exec('bake model --no-validation --no-test --no-fixture --no-entity TodoTasks');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertStringContainsString('CounterCache', $result);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test bake() with a -plugin param
     *
     * @return void
     */
    public function testBakeEntityWithPlugin()
    {
        $this->_loadTestPlugin('BakeTest');
        $path = Plugin::path('BakeTest');

        $this->generatedFile = $path . 'src/Model/Entity/User.php';

        $this->exec('bake model --no-validation --no-test --no-fixture --no-table BakeTest.Users');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Tests baking a table with rules
     *
     * @return void
     */
    public function testBakeWithRulesUnique()
    {
        $this->generatedFiles = [
            APP . 'Model/Table/UsersTable.php',
        ];
        $this->exec('bake model --no-test --no-fixture --no-entity Users');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);

        $result = file_get_contents($this->generatedFiles[0]);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    public function testBakeUpdateTableNoFile(): void
    {
        $this->generatedFile = APP . 'Model/Table/TodoItemsTable.php';
        $this->exec('bake model --no-entity --no-test --no-fixture --connection test --update TodoItems');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFile));
    }

    public function testBakeUpdateTable(): void
    {
        $existing = <<<'PARSE'
<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Table;

use App\SomeInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use RuntimeException as CustomException; // should be kept

/**
 * TodoItems Model
 */
class TodoItemsTable extends Table implements SomeInterface
{
    /**
     * @var int
     */
    protected const MY_CONST = 1;

    /**
     * @var string
     */
    protected $myProperty = 'string';

    public function validationDefault(Validator $validator): Validator
    {
        // should be overwritten
        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        // generation of this function is disabled by --no-rules and should stay

        return $rules;
    }

    /**
     */
    public function findByPriority(Query $query): Query
    {
        throw new CustomException();

        return $query;
    }
}
PARSE;

        $this->generatedFile = APP . 'Model/Table/TodoItemsTable.php';
        file_put_contents($this->generatedFile, $existing);
        $this->exec('bake model --no-rules --no-entity --no-test --no-fixture --update --force TodoItems');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFile));
    }

    public function testBakeUpdateEntity(): void
    {
        $existing = <<<'PARSE'
<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Entity;

use Authorization\IdentityInterface;
use MyApp\Test;

class TodoItem implements IdentityInterface
{
    /**
     * @var int
     */
    protected const MY_CONST = 1;

    protected $_accessible = [
        // should not overwritten
    ];

    /**
     * @var string
     */
    protected $myProperty = 'string';

    protected function _getName(): string
    {
        return 'name';
    }
}
PARSE;

        $this->generatedFile = APP . 'Model/Entity/TodoItem.php';
        file_put_contents($this->generatedFile, $existing);
        $this->exec('bake model --no-table --no-fields --hidden "user_id" --no-test --no-fixture --update --force TodoItems');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', file_get_contents($this->generatedFile));
    }

    /**
     * test that execute with no args
     *
     * @return void
     */
    public function testMainNoArgs()
    {
        $this->exec('bake model');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertOutputContains('Choose a model to bake from the following:');
    }

    /**
     * test that execute passes runs bake depending with named model.
     *
     * @return void
     */
    public function testMainWithNamedModel()
    {
        $this->generatedFiles = [
            APP . 'Model/Table/UsersTable.php',
            APP . 'Model/Entity/User.php',
            ROOT . 'tests/TestCase/Model/Table/UsersTableTest.php',
            ROOT . 'tests/Fixture/UsersFixture.php',
        ];
        $this->exec('bake model --connection test users');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
    }

    /**
     * data provider for testMainWithNamedModelVariations
     *
     * @return void
     */
    public static function nameVariations()
    {
        return [
            ['TodoItems'], ['todo_items'],
        ];
    }

    /**
     * test that execute passes with different inflections of the same name.
     *
     * @dataProvider nameVariations
     * @return void
     */
    public function testMainWithNamedModelVariations($name)
    {
        $this->generatedFiles = [
            APP . 'Model/Table/TodoItemsTable.php',
            APP . 'Model/Entity/TodoItem.php',
            ROOT . 'tests/TestCase/Model/Table/TodoItemsTableTest.php',
            ROOT . 'tests/Fixture/TodoItemsFixture.php',
        ];
        $this->exec("bake model --connection test {$name}");

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
    }

    /**
     * test baking nullable foreign keys
     *
     * @return void
     */
    public function testBakeTableNullableForeignKey()
    {
        $this->generatedFiles = [
            APP . 'Model/Table/TestBakeArticlesTable.php',
        ];

        $validation = [
            'id' => [
                'valid' => [
                    'rule' => 'numeric',
                    'args' => [],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => ['create'],
                ],
            ],
            'name' => [
                'valid' => [
                    'rule' => 'scalar',
                    'args' => [],
                ],
                'maxLength' => [
                    'rule' => 'maxLength',
                    'args' => [
                        100,
                        'Name must be shorter than 100 characters.',
                    ],
                ],
                'requirePresense' => [
                    'rule' => 'requirePresence',
                    'args' => ['create'],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => [null, false],
                ],
            ],
            'count' => [
                'valid' => [
                    'rule' => 'nonNegativeInteger',
                    'args' => [],
                ],
                'requirePresense' => [
                    'rule' => 'requirePresence',
                    'args' => ['create'],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => [null, false],
                ],
            ],
            'price' => [
                'valid' => [
                    'rule' => 'greaterThanOrEqual',
                    'args' => [
                        0,
                    ],
                ],
                'requirePresense' => [
                    'rule' => 'requirePresence',
                    'args' => ['create'],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => [null, false],
                ],
            ],
            'email' => [
                'valid' => [
                    'rule' => 'email',
                    'args' => [],
                ],
                'unique' => [
                    'rule' => 'validateUnique',
                    'provider' => 'table',
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => [],
                ],
            ],
            'image' => [
                'uploadedFile' => [
                    'rule' => 'uploadedFile',
                    'args' => [
                        [
                            'optional' => true,
                            'types' => ['image/jpeg'],
                        ],
                    ],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyFile',
                    'args' => [],
                ],
            ],
        ];

        $command = new ModelCommand();
        $command->connection = 'test';

        $name = 'TestBakeArticles';
        $args = new Arguments([$name], ['table' => 'bake_articles', 'force' => true], []);
        $io = new ConsoleIo($this->_out, $this->_err, $this->_in);

        $table = $command->getTable($name, $args);
        $tableObject = $command->getTableObject($name, $table);
        $data = $command->getTableContext($tableObject, $table, $name, $args, $io);
        $data['validation'] = $validation;
        $data['associations'] = [
            'belongsTo' => [],
            'hasMany' => [],
            'belongsToMany' => [],
        ];
        $data['rulesChecker'] = [];
        $command->bakeTable($tableObject, $data, $args, $io);

        $result = file_get_contents($this->generatedFiles[0]);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }
}
