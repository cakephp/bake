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

use Bake\Command\ModelCommand;
use Bake\Test\TestCase\TestCase;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlite;
use Cake\Database\Driver\Sqlserver;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

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
     * @var array
     */
    protected $fixtures = [
        'core.Comments',
        'core.Tags',
        'core.ArticlesTags',
        'plugin.Bake.TodoTasks',
        'plugin.Bake.TodoItems',
        'plugin.Bake.TodoLabels',
        'plugin.Bake.TodoItemsTodoLabels',
        'plugin.Bake.CategoryThreads',
        'plugin.Bake.Invitations',
        'plugin.Bake.NumberTrees',
        'plugin.Bake.Users',
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

        TableRegistry::getTableLocator()->clear();
    }

    /**
     * teardown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        TableRegistry::getTableLocator()->clear();
    }

    /**
     * Test that listAll uses the connection property
     *
     * @return void
     */
    public function testListAllConnection()
    {
        $this->exec('bake model --connection test');

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertOutputContains('- TodoItems');
        $this->assertOutputContains('- TodoTasks');
        $this->assertOutputContains('- TodoLabels');
        $this->assertOutputContains('- TodoItemsTodoLabels');
        $this->assertOutputContains('- CategoryThreads');
    }

    /**
     * Test getName() method.
     *
     * @return void
     */
    public function testGetTable()
    {
        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getTable('TodoItems', $args);
        $this->assertEquals('todo_items', $result);

        $args = new Arguments([], ['table' => 'items'], []);
        $result = $command->getTable('TodoItems', $args);
        $this->assertEquals('items', $result);
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
        $this->assertEquals('todo_items', $result->getTable());
        $this->assertEquals('TodoItems', $result->getAlias());

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
        $this->assertEquals('my_prefix_todo_items', $result->getTable());
        $this->assertInstanceOf('Cake\ORM\Table', $result);
        $this->assertEquals('TodoItems', $result->getAlias());

        $command->plugin = 'BakeTest';
        $result = $command->getTableObject('Authors', 'todo_items');
        $this->assertEquals('my_prefix_todo_items', $result->getTable());
        $this->assertInstanceOf('BakeTest\Model\Table\AuthorsTable', $result);
    }

    /**
     * Test applying associations.
     *
     * @return void
     */
    public function testApplyAssociations()
    {
        $articles = TableRegistry::getTableLocator()->get('TodoItems');
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
        $expected = ['users', 'todotasks', 'todolabels'];
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
        $articles = TableRegistry::getTableLocator()->get('Articles');
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
        $items = TableRegistry::getTableLocator()->get('TodoItems');

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
        $articles = TableRegistry::getTableLocator()->get('BakeArticle');
        $this->assertEquals([], $command->getAssociations($articles, $arguments, $io));
    }

    /**
     * Test getAssociations in a plugin
     *
     * @return void
     */
    public function testGetAssociationsPlugin()
    {
        $items = TableRegistry::getTableLocator()->get('TodoItems');
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
     * Test that association generation ignores `_id` fields
     *
     * @return void
     */
    public function testGetAssociationsIgnoreUnderscoreId()
    {
        $model = TableRegistry::getTableLocator()->get('BakeComments');
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
        $model = TableRegistry::getTableLocator()->get('Invitations');
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
        $model = TableRegistry::getTableLocator()->get('TodoItems');
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

        $model = TableRegistry::getTableLocator()->get('CategoryThreads');
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
        $model = TableRegistry::getTableLocator()->get('Invitations');
        $command = new ModelCommand();
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
        $model = TableRegistry::getTableLocator()->get('TodoItemsTodoLabels');
        $command = new ModelCommand();
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
        $model = TableRegistry::getTableLocator()->get('TodoItems');
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
        $model = TableRegistry::getTableLocator()->get('Articles');
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
     * test that hasOne and/or hasMany relations are generated properly.
     *
     * @return void
     */
    public function testHasManyGeneration()
    {
        $command = new ModelCommand();
        $command->connection = 'test';

        $model = TableRegistry::getTableLocator()->get('TodoItems');
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

        $model = TableRegistry::getTableLocator()->get('CategoryThreads');
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
        $model = TableRegistry::getTableLocator()->get('TodoItems');
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
        $model = TableRegistry::getTableLocator()->get('TodoItems');
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
        $model = TableRegistry::getTableLocator()->get('TodoItems');

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
        $model = TableRegistry::getTableLocator()->get('TodoItems');
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
        $model = TableRegistry::getTableLocator()->get('TodoItems');

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
        $model = TableRegistry::getTableLocator()->get('Users');

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
        $model = TableRegistry::getTableLocator()->get('Users');

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
        $model = TableRegistry::getTableLocator()->get('Users');

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
        $model = TableRegistry::getTableLocator()->get('TodoItems');
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
        $model = TableRegistry::getTableLocator()->get('TodoItems');
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

        $model = TableRegistry::getTableLocator()->get('TodoItems');

        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getValidation($model, [], $args);
        $expected = [
            'user_id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ["'create'"]],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'title' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ["'create'"]],
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
            'id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => ['null', "'create'"]],
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

        $model = TableRegistry::getTableLocator()->get('TodoTasks');
        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getValidation($model, [], $args);
        $expected = [
            'todo_item_id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ["'create'"]],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'title' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ["'create'"]],
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
            'uid' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => ['null', "'create'"]],
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
        $model = TableRegistry::getTableLocator()->get('TodoItems');
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
            'requirePresence' => ['rule' => 'requirePresence', 'args' => ["'create'"]],
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

        $model = TableRegistry::getTableLocator()->get('NumberTrees');
        $args = new Arguments([], [], []);
        $command = new ModelCommand();
        $result = $command->getValidation($model, [], $args);
        $expected = [
            'id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => ['null', "'create'"]],
            ],
            'name' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ["'create'"]],
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

        $model = TableRegistry::getTableLocator()->get('NumberTrees');
        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getValidation($model, [], $args);
        $expected = [
            'id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => ['null', "'create'"]],
            ],
            'name' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ["'create'"]],
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
    public function testGetValidationExcludeForeignKeys()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Postgres, 'Incompatible with postgres');
        $this->skipIf($driver instanceof Sqlserver, 'Incompatible with sqlserver');

        $model = TableRegistry::getTableLocator()->get('TodoItems');
        $associations = [
            'belongsTo' => [
                'Users' => ['foreignKey' => 'user_id'],
            ],
        ];
        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getValidation($model, $associations, $args);
        $this->assertArrayNotHasKey('user_id', $result);
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

        $model = TableRegistry::getTableLocator()->get('TodoItems');
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
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ["'create'"]],
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
            'id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => ['null', "'create'"]],
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
        $model = TableRegistry::getTableLocator()->get('Users');
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
        $model = TableRegistry::getTableLocator()->get('Users');
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
            ],
            'country_id' => [
                'name' => 'existsIn',
                'extra' => 'Countries',
            ],
            'site_id' => [
                'name' => 'existsIn',
                'extra' => 'Sites',
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
        $model = TableRegistry::getTableLocator()->get('TodoItems');
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

        $model = TableRegistry::getTableLocator()->get('NumberTrees');
        $result = $command->getBehaviors($model);
        $this->assertEquals(['Tree' => []], $result);

        $model = TableRegistry::getTableLocator()->get('TodoItems');
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
        $model = TableRegistry::getTableLocator()->get('TodoItems');
        $command = new ModelCommand();
        $args = new Arguments([], [], []);
        $result = $command->getDisplayField($model, $args);
        $this->assertEquals('title', $result);

        $args = new Arguments([], ['display-field' => 'custom'], []);
        $result = $command->getDisplayField($model, $args);
        $this->assertEquals('custom', $result);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileNotExists(ROOT . 'tests/TestCase/Model/Table/TodoItemsTableTest.php');
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

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileNotExists(ROOT . 'tests/Fixture/TodoItemsFixture.php');
        $this->assertFileNotExists(ROOT . 'tests/TestCase/Model/Table/TodoItemsTableTest.php');
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

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileNotExists(ROOT . 'tests/Fixture/TodoItemsFixture.php');
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

        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
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
        $model = TableRegistry::getTableLocator()->get('TodoItems');
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

        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->generatedFile = $path . 'src/Model/Table/UsersTable.php';

        $this->exec('bake model --no-validation --no-test --no-fixture --no-entity -p BakeTest Users');

        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);

        $result = file_get_contents($this->generatedFiles[0]);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test that execute with no args
     *
     * @return void
     */
    public function testMainNoArgs()
    {
        $this->exec('bake model');

        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
    }
}
