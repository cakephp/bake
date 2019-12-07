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
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlite;
use Cake\Database\Driver\Sqlserver;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

/**
 * ModelTaskTest class
 */
class ModelTaskTest extends TestCase
{
    /**
     * Fixtures should be dropped after each tests
     *
     * @var bool
     */
    public $dropTables = true;

    /**
     * fixtures
     *
     * Don't sort this list alphabetically - otherwise there are table constraints
     * which fail when using postgres
     *
     * @var array
     */
    public $fixtures = [
        'core.Users',
        'core.CounterCacheUsers',
        'core.CounterCachePosts',
        'core.Comments',
        'core.Tags',
        'core.ArticlesTags',
        'plugin.Bake.BakeArticles',
        'plugin.Bake.BakeComments',
        'plugin.Bake.BakeArticlesBakeTags',
        'plugin.Bake.BakeTags',
        'plugin.Bake.CategoryThreads',
        'plugin.Bake.Invitations',
        'plugin.Bake.NumberTrees',
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
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Model' . DS;
        $io = $this->getMockBuilder('Cake\Console\ConsoleIo')
            ->disableOriginalConstructor()
            ->getMock();

        $this->Task = $this->getMockBuilder('Bake\Shell\Task\ModelTask')
            ->setMethods(['in', 'err', 'createFile', '_stop', '_checkUnitTest'])
            ->setConstructorArgs([$io])
            ->getMock();

        $this->Task->connection = 'test';
        $this->_setupOtherMocks();
        TableRegistry::getTableLocator()->clear();
    }

    /**
     * Setup a mock that has out mocked. Normally this is not used as it makes $this->at() really tricky.
     *
     * @return void
     */
    protected function _useMockedOut()
    {
        $io = $this->getMockBuilder('Cake\Console\ConsoleIo')
            ->disableOriginalConstructor()
            ->getMock();

        $this->Task = $this->getMockBuilder('Bake\Shell\Task\ModelTask')
            ->setMethods(['in', 'out', 'err', 'hr', 'createFile', '_stop', '_checkUnitTest'])
            ->setConstructorArgs([$io])
            ->getMock();

        $this->_setupOtherMocks();
    }

    /**
     * sets up the rest of the dependencies for Model Task
     *
     * @return void
     */
    protected function _setupOtherMocks()
    {
        $io = $this->getMockBuilder('Cake\Console\ConsoleIo')
            ->disableOriginalConstructor()
            ->getMock();

        $this->Task->Fixture = $this->getMockBuilder('Bake\Shell\Task\FixtureTask')
            ->setConstructorArgs([$io])
            ->setMethods(['bake', 'execute'])
            ->getMock();
        $this->Task->Test = $this->getMockBuilder('Bake\Shell\Task\TestTask')
            ->setConstructorArgs([$io])
            ->setMethods(['bake', 'execute'])
            ->getMock();
        $this->Task->BakeTemplate = new BakeTemplateTask($io);
        $this->Task->BakeTemplate->interactive = false;

        $this->Task->name = 'Model';
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
     * Test that listAll uses the connection property
     *
     * @return void
     */
    public function testListAllConnection()
    {
        $this->exec('bake model --connection test');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('- BakeArticles');
        $this->assertOutputContains('- BakeArticlesBakeTags');
        $this->assertOutputContains('- BakeTags');
        $this->assertOutputContains('- BakeComments');
        $this->assertOutputContains('- CategoryThreads');
    }

    /**
     * Test getName() method.
     *
     * @return void
     */
    public function testGetTable()
    {
        $result = $this->Task->getTable('BakeArticles');
        $this->assertEquals('bake_articles', $result);

        $this->Task->params['table'] = 'bake_articles';
        $result = $this->Task->getTable('Article');
        $this->assertEquals('bake_articles', $result);
    }

    /**
     * Test getting the a table class.
     *
     * @return void
     */
    public function testGetTableObject()
    {
        $result = $this->Task->getTableObject('Article', 'bake_articles');
        $this->assertInstanceOf('Cake\ORM\Table', $result);
        $this->assertEquals('bake_articles', $result->getTable());
        $this->assertEquals('Article', $result->getAlias());

        $this->Task->params['plugin'] = 'BakeTest';
        $result = $this->Task->getTableObject('Authors', 'bake_articles');
        $this->assertInstanceOf('BakeTest\Model\Table\AuthorsTable', $result);
    }

    /**
     * Test getting the a table class with a table prefix.
     *
     * @return void
     */
    public function testGetTableObjectPrefix()
    {
        $this->Task->tablePrefix = 'my_prefix_';

        $result = $this->Task->getTableObject('Article', 'bake_articles');
        $this->assertEquals('my_prefix_bake_articles', $result->getTable());
        $this->assertInstanceOf('Cake\ORM\Table', $result);
        $this->assertEquals('Article', $result->getAlias());

        $this->Task->params['plugin'] = 'BakeTest';
        $result = $this->Task->getTableObject('Authors', 'bake_articles');
        $this->assertEquals('my_prefix_bake_articles', $result->getTable());
        $this->assertInstanceOf('BakeTest\Model\Table\AuthorsTable', $result);
    }

    /**
     * Test getAssociations with off flag.
     *
     * @return void
     */
    public function testGetAssociationsNoFlag()
    {
        $this->Task->params['no-associations'] = true;
        $articles = TableRegistry::getTableLocator()->get('BakeArticle');
        $this->assertEquals([], $this->Task->getAssociations($articles));
    }

    /**
     * Test applying associations.
     *
     * @return void
     */
    public function testApplyAssociations()
    {
        $articles = TableRegistry::getTableLocator()->get('BakeArticles');
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
        $this->assertEquals([], $original);

        $this->Task->applyAssociations($articles, $assocs);
        $new = $articles->associations()->keys();
        $expected = ['bakeusers', 'bakecomments', 'baketags'];
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
        $this->Task->applyAssociations($articles, $assocs);
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
        $articles = TableRegistry::getTableLocator()->get('BakeArticles');
        $result = $this->Task->getAssociations($articles);
        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'BakeUsers',
                    'foreignKey' => 'bake_user_id',
                    'joinType' => 'INNER',
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
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getAssociations in a plugin
     *
     * @return void
     */
    public function testGetAssociationsPlugin()
    {
        $articles = TableRegistry::getTableLocator()->get('BakeArticles');
        $this->Task->plugin = 'TestBake';

        $result = $this->Task->getAssociations($articles);
        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'BakeUsers',
                    'className' => 'TestBake.BakeUsers',
                    'foreignKey' => 'bake_user_id',
                    'joinType' => 'INNER',
                ],
            ],
            'hasMany' => [
                [
                    'alias' => 'BakeComments',
                    'className' => 'TestBake.BakeComments',
                    'foreignKey' => 'bake_article_id',
                ],
            ],
            'belongsToMany' => [
                [
                    'alias' => 'BakeTags',
                    'className' => 'TestBake.BakeTags',
                    'foreignKey' => 'bake_article_id',
                    'joinTable' => 'bake_articles_bake_tags',
                    'targetForeignKey' => 'bake_tag_id',
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
        $result = $this->Task->getAssociations($model);
        $expected = [
            'hasMany' => [],
            'belongsTo' => [],
            'belongsToMany' => [],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * test that belongsTo generation works.
     *
     * @return void
     */
    public function testBelongsToGeneration()
    {
        $model = TableRegistry::getTableLocator()->get('BakeComments');
        $result = $this->Task->findBelongsTo($model, []);
        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'BakeArticles',
                    'foreignKey' => 'bake_article_id',
                    'joinType' => 'INNER',
                ],
                [
                    'alias' => 'BakeUsers',
                    'foreignKey' => 'bake_user_id',
                    'joinType' => 'INNER',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);

        $model = TableRegistry::getTableLocator()->get('CategoryThreads');
        $result = $this->Task->findBelongsTo($model, []);
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

        $this->Task->plugin = 'Blog';
        $result = $this->Task->findBelongsTo($model, []);
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
        $result = $this->Task->findBelongsTo($model, []);
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
        $model = TableRegistry::getTableLocator()->get('ArticlesTags');
        $result = $this->Task->findBelongsTo($model, []);
        $expected = [
            'belongsTo' => [
                [
                    'alias' => 'Articles',
                    'foreignKey' => 'article_id',
                    'joinType' => 'INNER',
                ],
                [
                    'alias' => 'Tags',
                    'foreignKey' => 'tag_id',
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
        $model = TableRegistry::getTableLocator()->get('Articles');
        $model->setSchema([
            'id' => ['type' => 'integer'],
            'thing_id_field' => ['type' => 'integer'],
        ]);
        $result = $this->Task->findBelongsTo($model, []);
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
        $result = $this->Task->findBelongsTo($model, []);
        $this->assertEquals([], $result);
    }

    /**
     * test that hasOne and/or hasMany relations are generated properly.
     *
     * @return void
     */
    public function testHasManyGeneration()
    {
        $this->Task->connection = 'test';
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $result = $this->Task->findHasMany($model, []);
        $expected = [
            'hasMany' => [
                [
                    'alias' => 'BakeComments',
                    'foreignKey' => 'bake_article_id',
                ],
            ],
        ];
        $this->assertEquals($expected, $result);

        $model = TableRegistry::getTableLocator()->get('CategoryThreads');
        $result = $this->Task->findHasMany($model, []);
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

        $this->Task->plugin = 'Blog';
        $result = $this->Task->findHasMany($model, []);
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
        $this->Task->connection = 'test';
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $result = $this->Task->findBelongsToMany($model, []);
        $expected = [
            'belongsToMany' => [
                [
                    'alias' => 'BakeTags',
                    'foreignKey' => 'bake_article_id',
                    'joinTable' => 'bake_articles_bake_tags',
                    'targetForeignKey' => 'bake_tag_id',
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
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $model->belongsTo('BakeUsers');
        $model->hasMany('BakeTest.Authors');
        $model->getSchema()->setColumnType('created', 'timestamp');
        $model->getSchema()->setColumnType('updated', 'timestamp');

        $result = $this->Task->getEntityPropertySchema($model);
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
            'rating' => [
                'kind' => 'column',
                'type' => 'float',
                'null' => false,
            ],
            'score' => [
                'kind' => 'column',
                'type' => 'decimal',
                'null' => false,
            ],
            'created' => [
                'kind' => 'column',
                'type' => 'timestamp',
                'null' => true,
            ],
            'bake_user_id' => [
                'kind' => 'column',
                'type' => 'integer',
                'null' => false,
            ],
            'published' => [
                'kind' => 'column',
                'type' => 'boolean',
                'null' => false,
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
                'type' => '\App\Model\Entity\BakeUser',
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
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $model->belongsTo('BakeUser');

        $result = $this->Task->getFields($model);
        $expected = [
            'bake_user_id',
            'title',
            'body',
            'rating',
            'score',
            'published',
            'created',
            'updated',
            'bake_user',
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
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $this->Task->params['no-fields'] = true;
        $result = $this->Task->getFields($model);
        $this->assertFalse($result);
    }

    /**
     * Test getting accessible fields with a whitelist
     *
     * @return void
     */
    public function testGetFieldsWhiteList()
    {
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $this->Task->params['fields'] = 'id, title  , , body ,  created';
        $result = $this->Task->getFields($model);
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
        $result = $this->Task->getHiddenFields($model);
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
        $this->Task->params['no-hidden'] = true;
        $result = $this->Task->getHiddenFields($model);
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
        $this->Task->params['hidden'] = 'id, title  , , body ,  created';
        $result = $this->Task->getHiddenFields($model);
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
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $result = $this->Task->getPrimaryKey($model);
        $expected = ['id'];
        $this->assertEquals($expected, $result);

        $this->Task->params['primary-key'] = 'id, , account_id';
        $result = $this->Task->getPrimaryKey($model);
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
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $this->Task->params['no-validation'] = true;
        $result = $this->Task->getValidation($model);
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

        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $result = $this->Task->getValidation($model);
        $expected = [
            'id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => ['null', "'create'"]],
            ],
            'bake_user_id' => [
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
            'rating' => [
                'numeric' => ['rule' => 'numeric', 'args' => []],
                'greaterThanOrEqual' => [
                    'rule' => 'greaterThanOrEqual',
                    'args' => [
                        0,
                    ],
                ],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'score' => [
                'decimal' => ['rule' => 'decimal', 'args' => []],
                'greaterThanOrEqual' => [
                    'rule' => 'greaterThanOrEqual',
                    'args' => [
                        0,
                    ],
                ],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'published' => [
                'boolean' => ['rule' => 'boolean', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
        ];
        $this->assertEquals($expected, $result);

        $model = TableRegistry::getTableLocator()->get('BakeComments');
        $result = $this->Task->getValidation($model);
        $expected = [
            'bake_article_id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ["'create'"]],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'bake_user_id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ["'create'"]],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'comment' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => []],
            ],
            'published' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'maxLength' => ['rule' => 'maxLength', 'args' => [1]],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => []],
            ],
            'otherid' => [
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

        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $result = $this->Task->getValidation($model);
        $expected = [
            'bake_user_id' => [
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
            'rating' => [
                'numeric' => ['rule' => 'numeric', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'score' => [
                'decimal' => ['rule' => 'decimal', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'published' => [
                'boolean' => ['rule' => 'boolean', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => ['null', "'create'"]],
            ],
        ];
        $this->assertEquals($expected, $result);

        $model = TableRegistry::getTableLocator()->get('BakeComments');
        $result = $this->Task->getValidation($model);
        $expected = [
            'bake_article_id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ["'create'"]],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'bake_user_id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'requirePresence' => ['rule' => 'requirePresence', 'args' => ["'create'"]],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'comment' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => []],
            ],
            'published' => [
                'scalar' => ['rule' => 'scalar', 'args' => []],
                'maxLength' => ['rule' => 'maxLength', 'args' => [1]],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => []],
            ],
            'otherid' => [
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
        $model = TableRegistry::getTableLocator()->get('BakeComments');
        $schema = $model->getSchema();
        $schema
            ->addColumn('release_date', ['type' => 'datetime'])
            ->addConstraint('unique_date', [
                'columns' => ['release_date'],
                'type' => 'unique',
            ]);
        $result = $this->Task->getValidation($model);
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
        $result = $this->Task->getValidation($model);
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
        $result = $this->Task->getValidation($model);
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

        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $associations = [
            'belongsTo' => [
                'BakeUsers' => ['foreignKey' => 'bake_user_id'],
            ],
        ];
        $result = $this->Task->getValidation($model, $associations);
        $expected = [
            'id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => ['null', "'create'"]],
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
            'rating' => [
                'numeric' => ['rule' => 'numeric', 'args' => []],
                'greaterThanOrEqual' => [
                    'rule' => 'greaterThanOrEqual',
                    'args' => [
                        0,
                    ],
                ],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'score' => [
                'decimal' => ['rule' => 'decimal', 'args' => []],
                'greaterThanOrEqual' => [
                    'rule' => 'greaterThanOrEqual',
                    'args' => [
                        0,
                    ],
                ],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'published' => [
                'boolean' => [
                    'rule' => 'boolean',
                    'args' => [],
                ],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
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

        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $associations = [
            'belongsTo' => [
                'BakeUsers' => ['foreignKey' => 'bake_user_id'],
            ],
        ];
        $result = $this->Task->getValidation($model, $associations);
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
            'published' => [
                'boolean' => ['rule' => 'boolean', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'id' => [
                'integer' => ['rule' => 'integer', 'args' => []],
                'allowEmpty' => ['rule' => 'allowEmptyString', 'args' => ['null', "'create'"]],
            ],
            'rating' => [
                'numeric' => ['rule' => 'numeric', 'args' => []],
                'notEmpty' => ['rule' => 'notEmptyString', 'args' => []],
            ],
            'score' => [
                'decimal' => ['rule' => 'decimal', 'args' => []],
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
        $model = TableRegistry::getTableLocator()->get('Users');
        $this->Task->params['no-rules'] = true;
        $result = $this->Task->getRules($model, []);
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
        $result = $this->Task->getRules($model, $associations);
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
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $model->getSchema()->addConstraint('unique_title', [
            'type' => 'unique',
            'columns' => ['title'],
        ]);
        $model->getSchema()->addConstraint('ignored_constraint', [
            'type' => 'unique',
            'columns' => ['title', 'bake_user_id'],
        ]);

        $result = $this->Task->getRules($model, []);
        $expected = [
            'title' => [
                'name' => 'isUnique',
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * test non interactive doActsAs
     *
     * @return void
     */
    public function testGetBehaviors()
    {
        $model = TableRegistry::getTableLocator()->get('NumberTrees');
        $result = $this->Task->getBehaviors($model);
        $this->assertEquals(['Tree' => []], $result);

        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $result = $this->Task->getBehaviors($model);
        $this->assertEquals(['Timestamp' => []], $result);

        TableRegistry::getTableLocator()->clear();
        TableRegistry::getTableLocator()->get('Users', [
            'table' => 'counter_cache_users',
        ]);
        $model = TableRegistry::getTableLocator()->get('Posts', [
            'table' => 'counter_cache_posts',
        ]);
        $behaviors = $this->Task->getBehaviors($model);

        $behaviors['Translate'] = [
            'defaultLocale' => "'fr_FR'",
            'implementedFinders' => ['translations' => "'findTranslations'"],
        ];

        $result = $this->Task->bakeTable($model, ['behaviors' => $behaviors]);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Test getDisplayField() method.
     *
     * @return void
     */
    public function testGetDisplayField()
    {
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $result = $this->Task->getDisplayField($model);
        $this->assertEquals('title', $result);

        $this->Task->params['display-field'] = 'custom';
        $result = $this->Task->getDisplayField($model);
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
            APP . 'Model/Table/BakeTagsTable.php',
            APP . 'Model/Entity/BakeTag.php',
            ROOT . 'tests/Fixture/BakeTagsFixture.php',
        ];
        $this->exec('bake model --no-test bake_tags');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
    }

    /**
     * Ensure that the fixture baking can be disabled
     *
     * @return void
     */
    public function testBakeFixtureDisabled()
    {
        $this->generatedFiles = [
            APP . 'Model/Table/BakeTagsTable.php',
            APP . 'Model/Entity/BakeTag.php',
        ];
        $this->exec('bake model --no-test --no-fixture bake_tags');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileNotExists(ROOT . 'tests/Fixture/BakeTagsFixture.php');
        $this->assertFileNotExists(ROOT . 'tests/TestCase/Model/Table/BakeTagsTableTest.php');
    }

    /**
     * Ensure that the test object is correctly called.
     *
     * @return void
     */
    public function testBakeTest()
    {
        $this->generatedFiles = [
            APP . 'Model/Table/BakeTagsTable.php',
            APP . 'Model/Entity/BakeTag.php',
            ROOT . 'tests/TestCase/Model/Table/BakeTagsTableTest.php',
        ];
        $this->exec('bake model --no-fixture bake_tags');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileNotExists(ROOT . 'tests/Fixture/BakeTagsFixture.php');
    }

    /**
     * test baking validation
     *
     * @return void
     */
    public function testBakeTableValidation()
    {
        $validation = [
            'id' => [
                'valid' => [
                    'rule' => 'numeric',
                    'args' => [],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => ["'create'"],
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
                        "'Name must be shorter than 100 characters.'",
                    ],
                ],
                'requirePresense' => [
                    'rule' => 'requirePresence',
                    'args' => ["'create'"],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => ['false'],
                ],
            ],
            'count' => [
                'valid' => [
                    'rule' => 'nonNegativeInteger',
                    'args' => [],
                ],
                'requirePresense' => [
                    'rule' => 'requirePresence',
                    'args' => ["'create'"],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => ['false'],
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
                    'args' => ["'create'"],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyString',
                    'args' => ['false'],
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
                'uploadError' => [
                    'rule' => 'uploadError',
                    'args' => ['true'],
                ],
                'uploadedFile' => [
                    'rule' => 'uploadedFile',
                    'args' => [
                        [
                            'optional' => 'true',
                            'types' => ["'image/jpeg'"],
                        ],
                    ],
                ],
                'allowEmpty' => [
                    'rule' => 'allowEmptyFile',
                    'args' => [],
                ],
            ],
        ];
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $result = $this->Task->bakeTable($model, compact('validation'));
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking with table config and ensure that prefixes are ignored.
     *
     * @return void
     */
    public function testBakeTableConfig()
    {
        $config = [
            'table' => 'articles',
            'primaryKey' => ['id'],
            'displayField' => 'title',
            'behaviors' => ['Timestamp' => ''],
            'connection' => 'website',
        ];

        $this->Task->params['prefix'] = 'Admin';
        $this->Task->expects($this->once())
            ->method('createFile')
            ->with($this->stringContains('App' . DS . 'Model' . DS . 'Table' . DS . 'BakeArticlesTable.php'));

        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $result = $this->Task->bakeTable($model, $config);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking relations
     *
     * @return void
     */
    public function testBakeTableRelations()
    {
        $associations = [
            'belongsTo' => [
                [
                    'alias' => 'SomethingElse',
                    'foreignKey' => 'something_else_id',
                ],
                [
                    'alias' => 'BakeUser',
                    'foreignKey' => 'bake_user_id',
                ],
            ],
            'hasMany' => [
                [
                    'alias' => 'BakeComment',
                    'foreignKey' => 'parent_id',
                ],
            ],
            'belongsToMany' => [
                [
                    'alias' => 'BakeTag',
                    'foreignKey' => 'bake_article_id',
                    'joinTable' => 'bake_articles_bake_tags',
                    'targetForeignKey' => 'bake_tag_id',
                ],
            ],
        ];
        $associationInfo = [
            'SomethingElse' => ['targetFqn' => '\App\Model\Table\SomethingElseTable'],
            'BakeUser' => ['targetFqn' => '\App\Model\Table\BakeUserTable'],
            'BakeComment' => ['targetFqn' => '\App\Model\Table\BakeCommentTable'],
            'BakeTag' => ['targetFqn' => '\App\Model\Table\BakeTagTable'],
        ];
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $result = $this->Task->bakeTable($model, compact('associations', 'associationInfo'));
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

        $this->assertExitCode(Shell::CODE_SUCCESS);
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

        $this->assertExitCode(Shell::CODE_SUCCESS);
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
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $model->belongsTo('BakeUsers');
        $model->hasMany('BakeTest.Authors');
        $model->getSchema()->addColumn('array_type', [
            'type' => 'array',
        ]);
        $model->getSchema()->addColumn('json_type', [
            'type' => 'json',
        ]);
        $model->getSchema()->addColumn('unknown_type', [
            'type' => 'unknownType',
        ]);

        $config = [
            'fields' => false,
            'propertySchema' => $this->Task->getEntityPropertySchema($model),
        ];

        $this->generatedFile = APP . 'Model/Entity/BakeArticle.php';
        $this->exec('bake model --no-test --no-fixture --no-table --no-fields bake_articles');

        $this->assertExitCode(Shell::CODE_SUCCESS);
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
        $config = [
            'primaryKey' => ['id'],
            'fields' => null,
        ];
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $result = $this->Task->bakeEntity($model, $config);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking an entity class with no accessible fields.
     *
     * @return void
     */
    public function testBakeEntityNoFields()
    {
        $this->generatedFile = APP . 'Model/Entity/BakeArticle.php';
        $this->exec('bake model --no-test --no-fixture --no-table --no-fields bake_articles');

        $this->assertExitCode(Shell::CODE_SUCCESS);
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
        $this->generatedFile = APP . 'Model/Entity/BakeArticle.php';
        $this->exec('bake model --no-test --no-fixture --no-table --fields id,title,body,created bake_articles');

        $this->assertExitCode(Shell::CODE_SUCCESS);
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

        $this->assertExitCode(Shell::CODE_SUCCESS);
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

        $this->assertExitCode(Shell::CODE_SUCCESS);
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

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
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

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Tests baking a table with rules
     *
     * @return void
     */
    public function testBakeWithRules()
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
        $rulesChecker = $this->Task->getRules($model, $associations);
        $result = $this->Task->bakeTable($model, compact('rulesChecker'));
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

        $this->assertExitCode(Shell::CODE_SUCCESS);
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

        $this->assertExitCode(Shell::CODE_SUCCESS);
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
            ['BakeComments'], ['bake_comments'],
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
            APP . 'Model/Table/BakeCommentsTable.php',
            APP . 'Model/Entity/BakeComment.php',
            ROOT . 'tests/TestCase/Model/Table/BakeCommentsTableTest.php',
            ROOT . 'tests/Fixture/BakeCommentsFixture.php',
        ];
        $this->exec("bake model --connection test {$name}");

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
    }

    /**
     * test that execute runs all() when args[0] = all
     *
     * @return void
     */
    public function testMainIntoAll()
    {
        $count = count($this->Task->listAll());
        if ($count != count($this->fixtures)) {
            $this->markTestSkipped('Additional tables detected.');
        }

        $this->Task->connection = 'test';

        $this->Task->Fixture->expects($this->exactly($count))
            ->method('bake');
        $this->Task->Test->expects($this->exactly($count))
            ->method('bake');

        $filename = $this->_normalizePath(APP . 'Model/Table/ArticlesTagsTable.php');
        $this->Task->expects($this->at(1))
            ->method('createFile')
            ->with($filename, $this->stringContains('class ArticlesTagsTable extends'));

        $filename = $this->_normalizePath(APP . 'Model/Entity/ArticlesTag.php');
        $this->Task->expects($this->at(2))
            ->method('createFile')
            ->with($filename, $this->stringContains('class ArticlesTag extends'));

        $filename = $this->_normalizePath(APP . 'Model/Table/BakeArticlesTable.php');
        $this->Task->expects($this->at(3))
            ->method('createFile')
            ->with($filename, $this->stringContains('class BakeArticlesTable extends'));

        $filename = $this->_normalizePath(APP . 'Model/Entity/BakeArticle.php');
        $this->Task->expects($this->at(4))
            ->method('createFile')
            ->with($filename, $this->stringContains('class BakeArticle extends'));

        $filename = $this->_normalizePath(APP . 'Model/Table/BakeArticlesBakeTagsTable.php');
        $this->Task->expects($this->at(5))
            ->method('createFile')
            ->with($filename, $this->stringContains('class BakeArticlesBakeTagsTable extends'));

        $filename = $this->_normalizePath(APP . 'Model/Entity/BakeArticlesBakeTag.php');
        $this->Task->expects($this->at(6))
            ->method('createFile')
            ->with($filename, $this->stringContains('class BakeArticlesBakeTag extends'));

        $filename = $this->_normalizePath(APP . 'Model/Table/BakeCommentsTable.php');
        $this->Task->expects($this->at(7))
            ->method('createFile')
            ->with($filename, $this->stringContains('class BakeCommentsTable extends'));

        $filename = $this->_normalizePath(APP . 'Model/Entity/BakeComment.php');
        $this->Task->expects($this->at(8))
            ->method('createFile')
            ->with($filename, $this->stringContains('class BakeComment extends'));

        $filename = $this->_normalizePath(APP . 'Model/Table/BakeTagsTable.php');
        $this->Task->expects($this->at(9))
            ->method('createFile')
            ->with($filename, $this->stringContains('class BakeTagsTable extends'));

        $filename = $this->_normalizePath(APP . 'Model/Entity/BakeTag.php');
        $this->Task->expects($this->at(10))
            ->method('createFile')
            ->with($filename, $this->stringContains('class BakeTag extends'));

        $filename = $this->_normalizePath(APP . 'Model/Table/CategoryThreadsTable.php');
        $this->Task->expects($this->at(11))
            ->method('createFile')
            ->with($filename, $this->stringContains('class CategoryThreadsTable extends'));

        $filename = $this->_normalizePath(APP . 'Model/Entity/CategoryThread.php');
        $this->Task->expects($this->at(12))
            ->method('createFile')
            ->with($filename, $this->stringContains('class CategoryThread extends'));

        $this->Task->all();
    }

    /**
     * test that skipTables changes how all() works.
     *
     * @return void
     */
    public function testSkipTablesAndAll()
    {
        if ($this->Task->listAll()[1] != 'bake_articles') {
            $this->markTestSkipped('Additional tables detected.');
        }

        $this->Task->connection = 'test';
        $this->Task->skipTables = ['articles_tags', 'bake_tags', 'counter_cache_posts'];

        $this->Task->Fixture->expects($this->atLeast(9))
            ->method('bake');
        $this->Task->Test->expects($this->atLeast(9))
            ->method('bake');

        $filename = $this->_normalizePath(APP . 'Model/Entity/BakeArticle.php');
        $this->Task->expects($this->at(1))
            ->method('createFile')
            ->with($filename);

        $filename = $this->_normalizePath(APP . 'Model/Entity/BakeArticlesBakeTag.php');
        $this->Task->expects($this->at(3))
            ->method('createFile')
            ->with($filename);

        $filename = $this->_normalizePath(APP . 'Model/Entity/BakeComment.php');
        $this->Task->expects($this->at(5))
            ->method('createFile')
            ->with($filename);

        $filename = $this->_normalizePath(APP . 'Model/Entity/BakeComment.php');
        $this->Task->expects($this->at(5))
            ->method('createFile')
            ->with($filename);

        $filename = $this->_normalizePath(APP . 'Model/Entity/CategoryThread.php');
        $this->Task->expects($this->at(7))
            ->method('createFile')
            ->with($filename);

        $filename = $this->_normalizePath(APP . 'Model/Entity/Comment.php');
        $this->Task->expects($this->at(9))
            ->method('createFile')
            ->with($filename);

        $filename = $this->_normalizePath(APP . 'Model/Entity/CounterCacheUser.php');
        $this->Task->expects($this->at(11))
            ->method('createFile')
            ->with($filename);

        $filename = $this->_normalizePath(APP . 'Model/Entity/Invitation.php');
        $this->Task->expects($this->at(13))
            ->method('createFile')
            ->with($filename);

        $filename = $this->_normalizePath(APP . 'Model/Entity/NumberTree.php');
        $this->Task->expects($this->at(15))
            ->method('createFile')
            ->with($filename);

        $this->Task->all();
    }

    /**
     * test finding referenced tables using constraints.
     *
     * @return void
     */
    public function testFindTableReferencedBy()
    {
        $invoices = TableRegistry::getTableLocator()->get('Invitations');
        $schema = $invoices->getSchema();
        $result = $this->Task->findTableReferencedBy($schema, 'not_there');
        $this->assertNull($result);

        $result = $this->Task->findTableReferencedBy($schema, 'sender_id');
        $this->assertEquals('users', $result);

        $result = $this->Task->findTableReferencedBy($schema, 'receiver_id');
        $this->assertEquals('users', $result);
    }

    /**
     * Tests collecting association info with default association configuration.
     *
     * @return void
     */
    public function testGetAssociationInfo()
    {
        $model = TableRegistry::getTableLocator()->get('BakeArticles');
        $model->belongsTo('BakeUsers');
        $model->hasMany('BakeTest.Authors');
        $model->hasMany('BakeTest.Publishers');

        $result = $this->Task->getAssociationInfo($model);

        $expected = [
            'BakeUsers' => [
                'targetFqn' => '\App\Model\Table\BakeUsersTable',
            ],
            'Authors' => [
                'targetFqn' => '\BakeTest\Model\Table\AuthorsTable',
            ],
            'Publishers' => [
                'targetFqn' => '\BakeTest\Model\Table\PublishersTable',
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests collecting association info with short classnames configured.
     *
     * @return void
     */
    public function testGetAssociationInfoShortClassName()
    {
        $model = TableRegistry::getTableLocator()->get('Authors');
        $model->belongsTo('BakeUsersAlias', [
            'className' => 'BakeTest.BakeUsers',
        ]);
        $model->hasMany('ArticlesAlias', [
            'className' => 'Articles',
        ]);
        $model->hasMany('BakeTestArticlesAlias', [
            'className' => 'BakeTest.BakeTestArticles',
        ]);
        $model->hasMany('PublishersAlias', [
            'className' => 'BakeTest.Publishers',
        ]);

        $result = $this->Task->getAssociationInfo($model);

        $expected = [
            'BakeUsersAlias' => [
                'targetFqn' => '\BakeTest\Model\Table\BakeUsersTable',
            ],
            'ArticlesAlias' => [
                'targetFqn' => '\App\Model\Table\ArticlesTable',
            ],
            'BakeTestArticlesAlias' => [
                'targetFqn' => '\BakeTest\Model\Table\BakeTestArticlesTable',
            ],
            'PublishersAlias' => [
                'targetFqn' => '\BakeTest\Model\Table\PublishersTable',
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests collecting association info with short classnames and a non-default namespace configured.
     *
     * @return void
     */
    public function testGetAssociationInfoShortClassNameNonDefaultAppNamespace()
    {
        Configure::write('App.namespace', 'Bake\Test\App');

        $model = TableRegistry::getTableLocator()->get('Authors');
        $model->hasMany('ArticlesAlias', [
            'className' => 'Articles',
        ]);

        $result = $this->Task->getAssociationInfo($model);

        $expected = [
            'Articles' => [
                'targetFqn' => '\Bake\Test\App\Model\Table\ArticlesTable',
            ],
            'ArticlesAlias' => [
                'targetFqn' => '\Bake\Test\App\Model\Table\ArticlesTable',
            ],
            'Roles' => [
                'targetFqn' => '\Bake\Test\App\Model\Table\RolesTable',
            ],
            'Profiles' => [
                'targetFqn' => '\Bake\Test\App\Model\Table\ProfilesTable',
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests collecting association info with fully qualified classnames configured.
     *
     * @return void
     */
    public function testGetAssociationInfoFqnClassName()
    {
        $model = TableRegistry::getTableLocator()->get('Authors');
        $model->hasMany('ArticlesAlias', [
            'className' => 'Bake\Test\App\Model\Table\ArticlesTable',
        ]);

        $result = $this->Task->getAssociationInfo($model);

        $expected = [
            'ArticlesAlias' => [
                'targetFqn' => '\Bake\Test\App\Model\Table\ArticlesTable',
            ],
        ];
        $this->assertEquals($expected, $result);
    }
}
