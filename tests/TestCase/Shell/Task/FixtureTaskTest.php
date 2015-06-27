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
use Bake\Test\TestCase\TestCase;
use Cake\Core\Plugin;
use Cake\ORM\TableRegistry;

/**
 * FixtureTaskTest class
 *
 */
class FixtureTaskTest extends TestCase
{
    /**
     * fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.articles',
        'core.comments',
        'plugin.bake.datatypes',
        'plugin.bake.binary_tests',
        'plugin.bake.bake_car',
        'core.users'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Task = $this->getMock(
            'Bake\Shell\Task\FixtureTask',
            ['in', 'err', 'createFile', '_stop', 'clear'],
            [$io]
        );
        $this->Task->Model = $this->getMock(
            'Bake\Shell\Task\ModelTask',
            ['in', 'out', 'err', 'createFile', 'getName', 'getTable', 'listAll'],
            [$io]
        );
        $this->Task->BakeTemplate = new BakeTemplateTask($io);
        $this->Task->BakeTemplate->interactive = false;
        $this->Task->BakeTemplate->initialize();

        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Fixture' . DS;
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
     * Test that initialize() copies the connection property over.
     *
     * @return void
     */
    public function testInitializeCopyConnection()
    {
        $this->assertEquals('', $this->Task->connection);
        $this->Task->params = ['connection' => 'test'];

        $this->Task->initialize();
        $this->assertEquals('test', $this->Task->connection);
    }

    /**
     * test that initialize sets the path
     *
     * @return void
     */
    public function testGetPath()
    {
        $this->assertPathEquals(ROOT . DS . 'tests' . DS . 'Fixture/', $this->Task->getPath());
    }

    /**
     * test generating a fixture with database rows.
     *
     * @return void
     */
    public function testImportRecordsFromDatabase()
    {
        $this->Task->connection = 'test';
        $this->Task->params = ['schema' => true, 'records' => true];

        $result = $this->Task->bake('Users');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test that connection gets set to the import options when a different connection is used.
     *
     * @return void
     */
    public function testImportOptionsAlternateConnection()
    {
        $this->Task->connection = 'test';
        $this->Task->params = ['schema' => true];
        $result = $this->Task->bake('Article');
        $this->assertContains("'connection' => 'test'", $result);
    }

    /**
     * Ensure that fixture data doesn't get overly escaped.
     *
     * @return void
     */
    public function testImportRecordsNoEscaping()
    {
        $articles = TableRegistry::get('Articles');
        $articles->updateAll(['body' => "Body \"value\""], []);

        $this->Task->connection = 'test';
        $this->Task->params = ['schema' => 'true', 'records' => true];
        $result = $this->Task->bake('Article');
        $this->assertContains("'body' => 'Body \"value\"'", $result, 'Data has bad escaping');
    }

    /**
     * Test the table option.
     *
     * @return void
     */
    public function testMainWithTableOption()
    {
        $this->Task->connection = 'test';
        $this->Task->params = ['table' => 'comments'];
        $filename = $this->_normalizePath(ROOT . DS . 'tests' . DS . 'Fixture/ArticlesFixture.php');

        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with($filename, $this->stringContains("public \$table = 'comments';"));

        $this->Task->main('articles');
    }

    /**
     * Test a singular table
     *
     * @return void
     */
    public function testMainWithSingularTable()
    {
        $this->Task->connection = 'test';
        $filename = $this->_normalizePath(ROOT . DS . 'tests' . DS . 'Fixture/CarFixture.php');

        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with($filename, $this->stringContains("public \$table = 'car';"));

        $this->Task->main('car');
    }

    /**
     * test that execute passes runs bake depending with named model.
     *
     * @return void
     */
    public function testMainWithPluginModel()
    {
        $this->Task->connection = 'test';
        $filename = $this->_normalizePath(APP . 'Plugin/FixtureTest/tests/Fixture/ArticlesFixture.php');

        Plugin::load('FixtureTest', ['path' => APP . 'Plugin/FixtureTest/']);

        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with($filename, $this->stringContains('class ArticlesFixture'));

        $this->Task->main('FixtureTest.Articles');
    }

    /**
     * test that execute runs all() when args[0] = all
     *
     * @return void
     */
    public function testMainIntoAll()
    {
        $this->Task->connection = 'test';
        $this->Task->Model->expects($this->any())
            ->method('listAll')
            ->will($this->returnValue(['articles', 'comments']));

        $filename = $this->_normalizePath(ROOT . DS . 'tests' . DS . 'Fixture/ArticlesFixture.php');
        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with($filename, $this->stringContains('class ArticlesFixture'));

        $filename = $this->_normalizePath(ROOT . DS . 'tests' . DS . 'Fixture/CommentsFixture.php');
        $this->Task->expects($this->at(1))
            ->method('createFile')
            ->with($filename, $this->stringContains('class CommentsFixture'));

        $this->Task->all();
    }

    /**
     * test using all() with -count and -records
     *
     * @return void
     */
    public function testAllWithCountAndRecordsFlags()
    {
        $this->Task->connection = 'test';
        $this->Task->params = ['count' => 10, 'records' => true];

        $this->Task->Model->expects($this->any())->method('listAll')
            ->will($this->returnValue(['Articles', 'comments']));

        $filename = $this->_normalizePath(ROOT . DS . 'tests' . DS . 'Fixture/ArticlesFixture.php');
        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with($filename, $this->stringContains("'title' => 'Third Article'"));

        $filename = $this->_normalizePath(ROOT . DS . 'tests' . DS . 'Fixture/CommentsFixture.php');
        $this->Task->expects($this->at(1))
            ->method('createFile')
            ->with($filename, $this->stringContains("'comment' => 'First Comment for First Article'"));

        $this->Task->expects($this->exactly(2))->method('createFile');

        $this->Task->all();
    }

    /**
     * test using all() with -schema
     *
     * @return void
     */
    public function testAllWithSchemaImport()
    {
        $this->Task->connection = 'test';
        $this->Task->params = ['schema' => true];

        $this->Task->Model->expects($this->any())->method('listAll')
            ->will($this->returnValue(['Articles', 'comments']));

        $filename = $this->_normalizePath(ROOT . DS . 'tests' . DS . 'Fixture/ArticlesFixture.php');
        $this->Task->expects($this->at(0))->method('createFile')
            ->with($filename, $this->stringContains("public \$import = ['model' => 'Articles'"));

        $filename = $this->_normalizePath(ROOT . DS . 'tests' . DS . 'Fixture/CommentsFixture.php');
        $this->Task->expects($this->at(1))->method('createFile')
            ->with($filename, $this->stringContains("public \$import = ['model' => 'Comments'"));
        $this->Task->expects($this->exactly(2))->method('createFile');

        $this->Task->all();
    }

    /**
     * test interactive mode of execute
     *
     * @return void
     */
    public function testMainNoArgs()
    {
        $this->Task->connection = 'test';

        $this->Task->Model->expects($this->any())
            ->method('listAll')
            ->will($this->returnValue(['articles', 'comments']));

        $filename = $this->_normalizePath(ROOT . '/tests/Fixture/ArticlesFixture.php');
        $this->Task->expects($this->never())
            ->method('createFile');

        $this->Task->main();
    }

    /**
     * Test that bake works
     *
     * @return void
     */
    public function testBake()
    {
        $this->Task->connection = 'test';

        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with($this->anything(), $this->logicalAnd(
                $this->stringContains('class ArticlesFixture extends TestFixture'),
                $this->stringContains('public $fields'),
                $this->stringContains('public $records'),
                $this->logicalNot($this->stringContains('public $import'))
            ));
        $result = $this->Task->main('Articles');
    }

    /**
     * test main() with importing records
     *
     * @return void
     */
    public function testMainImportRecords()
    {
        $this->Task->connection = 'test';
        $this->Task->params = ['import-records' => true];

        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with($this->anything(), $this->logicalAnd(
                $this->stringContains("public \$import = ['records' => true, 'connection' => 'test'];"),
                $this->logicalNot($this->stringContains('public $records'))
            ));

        $this->Task->main('Article');
    }

    /**
     * test main() with importing schema.
     *
     * @return void
     */
    public function testMainImportSchema()
    {
        $this->Task->connection = 'test';
        $this->Task->params = ['schema' => true, 'import-records' => true];

        $importString = "public \$import = ['model' => 'Article', 'records' => true, 'connection' => 'test'];";
        $this->Task->expects($this->once())
            ->method('createFile')
            ->with($this->anything(), $this->logicalAnd(
                $this->stringContains($importString),
                $this->logicalNot($this->stringContains('public $fields')),
                $this->logicalNot($this->stringContains('public $records'))
            ));
        $this->Task->bake('Article', 'comments');
    }

    /**
     * test record generation with float and binary types
     *
     * @return void
     */
    public function testRecordGenerationForBinaryAndFloat()
    {
        $this->Task->connection = 'test';

        $result = $this->Task->bake('Article', 'datatypes');
        $this->assertContains("'float_field' => 1", $result);
        $this->assertContains("'bool' => 1", $result);
        $this->assertContains("_constraints", $result);
        $this->assertContains("'primary' => ['type' => 'primary'", $result);
        $this->assertContains("'columns' => ['id']", $result);

        $result = $this->Task->bake('Article', 'binary_tests');
        $this->assertContains("'data' => 'Lorem ipsum dolor sit amet'", $result);
    }

    /**
     * Test that file generation includes headers and correct path for plugins.
     *
     * @return void
     */
    public function testGenerateFixtureFile()
    {
        $this->Task->connection = 'test';
        $filename = $this->_normalizePath(ROOT . DS . 'tests' . DS . 'Fixture/ArticlesFixture.php');

        $this->Task->expects($this->at(0))
            ->method('createFile')
            ->with($filename, $this->stringContains('ArticlesFixture'));

        $result = $this->Task->generateFixtureFile('Articles', []);
        $this->assertContains('<?php', $result);
        $this->assertContains('namespace App\Test\Fixture;', $result);
    }

    /**
     * test generating files into plugins.
     *
     * @return void
     */
    public function testGeneratePluginFixtureFile()
    {
        $this->_loadTestPlugin('TestBake');
        $root = Plugin::path('TestBake');

        $this->Task->connection = 'test';
        $this->Task->plugin = 'TestBake';
        $filename = $this->_normalizePath($root . 'tests/Fixture/ArticlesFixture.php');

        $this->Task->expects($this->at(0))->method('createFile')
            ->with($filename, $this->stringContains('class Articles'));

        $result = $this->Task->generateFixtureFile('Articles', []);
        $this->assertContains('<?php', $result);
        $this->assertContains('namespace TestBake\Test\Fixture;', $result);
    }
}
