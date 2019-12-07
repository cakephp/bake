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
use Cake\Console\Shell;
use Cake\Core\Plugin;
use Cake\Database\Driver\Postgres;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

/**
 * FixtureTaskTest class
 */
class FixtureTaskTest extends TestCase
{
    /**
     * fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.Articles',
        'core.Comments',
        'plugin.Bake.Datatypes',
        'plugin.Bake.BinaryTests',
        'plugin.Bake.BakeCar',
        'core.Users',
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
        $io = $this->getMockBuilder('Cake\Console\ConsoleIo')
            ->disableOriginalConstructor()
            ->getMock();

        $this->Task = $this->getMockBuilder('Bake\Shell\Task\FixtureTask')
            ->setMethods(['in', 'err', 'createFile', '_stop', 'clear'])
            ->setConstructorArgs([$io])
            ->getMock();
        $this->Task->Model = $this->getMockBuilder('Bake\Shell\Task\ModelTask')
            ->setMethods(['in', 'out', 'err', 'createFile', 'getName', 'getTable', 'listUnskipped'])
            ->setConstructorArgs([$io])
            ->getMock();
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
        $this->generatedFile = ROOT . 'tests/Fixture/UsersFixture.php';
        $this->exec('bake fixture --connection test --schema --records --count 5 Users');
        $this->assertExitCode(Shell::CODE_SUCCESS);

        $this->assertSameAsFile(
            __FUNCTION__ . '.php',
            file_get_contents($this->generatedFile)
        );
    }

    /**
     * test that connection gets set to the import options when a different connection is used.
     *
     * @return void
     */
    public function testImportOptionsAlternateConnection()
    {
        $this->generatedFile = ROOT . 'tests/Fixture/ArticleFixture.php';
        $this->exec('bake fixture --connection test --schema Article');
        $this->assertExitCode(Shell::CODE_SUCCESS);

        $this->assertContains("'connection' => 'test'", file_get_contents($this->generatedFile));
    }

    /**
     * Ensure that fixture data doesn't get overly escaped.
     *
     * @return void
     */
    public function testImportRecordsNoEscaping()
    {
        $articles = TableRegistry::getTableLocator()->get('Articles');
        $articles->updateAll(['body' => "Body \"value\""], []);

        $this->generatedFile = ROOT . 'tests/Fixture/ArticleFixture.php';
        $this->exec('bake fixture --connection test --schema --records Article');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileContains(
            "'body' => 'Body \"value\"'",
            $this->generatedFile,
            'Data has bad escaping'
        );
    }

    /**
     * Test the table option.
     *
     * @return void
     */
    public function testMainWithTableOption()
    {
        $this->generatedFile = ROOT . 'tests/Fixture/ArticlesFixture.php';
        $this->exec('bake fixture --connection test --table comments Articles');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileContains("public \$table = 'comments';", $this->generatedFile);
    }

    /**
     * Test a singular table
     *
     * @return void
     */
    public function testMainWithSingularTable()
    {
        $this->generatedFile = ROOT . 'tests/Fixture/CarFixture.php';
        $this->exec('bake fixture --connection test car');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileContains("public \$table = 'car';", $this->generatedFile);
    }

    /**
     * test that execute passes runs bake depending with named model.
     *
     * @return void
     */
    public function testMainWithPluginModel()
    {
        $this->loadPlugins(['FixtureTest' => ['path' => APP . 'Plugin/FixtureTest/']]);

        $this->generatedFile = APP . 'Plugin/FixtureTest/tests/Fixture/ArticlesFixture.php';
        $this->exec('bake fixture --connection test FixtureTest.Articles');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileContains("class ArticlesFixture", $this->generatedFile);
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
            ->method('listUnskipped')
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

        $this->Task->Model->expects($this->any())->method('listUnskipped')
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

        $this->Task->Model->expects($this->any())->method('listUnskipped')
            ->will($this->returnValue(['Articles', 'comments']));

        $filename = $this->_normalizePath(ROOT . DS . 'tests' . DS . 'Fixture/ArticlesFixture.php');
        $this->Task->expects($this->at(0))->method('createFile')
            ->with($filename, $this->stringContains("public \$import = ['table' => 'articles'"));

        $filename = $this->_normalizePath(ROOT . DS . 'tests' . DS . 'Fixture/CommentsFixture.php');
        $this->Task->expects($this->at(1))->method('createFile')
            ->with($filename, $this->stringContains("public \$import = ['table' => 'comments'"));
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
        $this->exec('bake fixture --connection test');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertOutputContains('- Articles');
        $this->assertOutputContains('- Comments');
    }

    /**
     * Test that bake works
     *
     * @return void
     */
    public function testBake()
    {
        $this->generatedFile = ROOT . 'tests/Fixture/ArticlesFixture.php';
        $this->exec('bake fixture --connection test Articles');

        $this->assertExitCode(Shell::CODE_SUCCESS);
        $this->assertFileContains('class ArticlesFixture extends TestFixture', $this->generatedFile);
        $this->assertFileContains('public $fields', $this->generatedFile);
        $this->assertFileContains('$this->records =', $this->generatedFile);
        $this->assertFileNotContains('public $import', $this->generatedFile);
    }

    /**
     * test main() with importing schema.
     *
     * @return void
     */
    public function testMainImportSchema()
    {
        $this->generatedFile = ROOT . 'tests/Fixture/CommentsFixture.php';
        $this->exec('bake fixture --connection test --schema Comments');

        $importString = "public \$import = ['table' => 'comments', 'connection' => 'test'];";
        $this->assertFileContains($importString, $this->generatedFile);
        $this->assertFileContains('$this->records =', $this->generatedFile);
        $this->assertFileNotContains('public $fields', $this->generatedFile);
    }

    /**
     * test record generation with various datatypes
     *
     * @return void
     */
    public function testRecordGenerationForDatatypes()
    {
        $this->generatedFile = ROOT . 'tests/Fixture/DatatypesFixture.php';
        $this->exec('bake fixture --connection test Datatypes');

        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertContains("'float_field' => 1", $result);
        $this->assertContains("'decimal_field' => 1.5", $result);
        $this->assertContains("'huge_int' => 1", $result);
        $this->assertContains("'small_int' => 1", $result);
        $this->assertContains("'tiny_int' => 1", $result);
        $this->assertContains("'bool' => 1", $result);
        $this->assertContains("_constraints", $result);
        $this->assertContains("'primary' => ['type' => 'primary'", $result);
        $this->assertContains("'columns' => ['id']", $result);
        $this->assertContains("'uuid' => ['type' => 'uuid'", $result);
        $this->assertRegExp("/(\s+)('uuid' => ')([a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89aAbB][a-f0-9]{3}-[a-f0-9]{12})(')/", $result);
    }

    /**
     * test record generation with float and binary types
     *
     * @return void
     */
    public function testRecordGenerationForBinaryType()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Postgres, 'Incompatible with postgres');

        $this->generatedFile = ROOT . 'tests/Fixture/BinaryTestsFixture.php';
        $this->exec('bake fixture --connection test BinaryTests');

        $this->assertFileContains("'data' => 'Lorem ipsum dolor sit amet'", $this->generatedFile);
        $this->assertFileContains("'byte' => 'L'", $this->generatedFile);
    }

    /**
     * test record generation with float and binary types
     *
     * @return void
     */
    public function testRecordGenerationForBinaryTypePostgres()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf(($driver instanceof Postgres) === false, 'Only compatible with postgres');

        $this->generatedFile = ROOT . 'tests/Fixture/BinaryTestsFixture.php';
        $this->exec('bake fixture --connection test BinaryTests');

        $this->assertFileContains("'data' => 'Lorem ipsum dolor sit amet'", $this->generatedFile);
        $this->assertFileContains("'byte' => 'Lorem ipsum dolor sit amet'", $this->generatedFile);
    }

    /**
     * Test that file generation works with remapped json types
     *
     * @return void
     */
    public function testGenerateFixtureFileRemappedJsonTypes()
    {
        $table = TableRegistry::getTableLocator()->get('Articles');
        $table->getSchema()->addColumn('body', ['type' => 'json']);
        $this->generatedFile = ROOT . 'tests/Fixture/ArticlesFixture.php';
        $this->exec('bake fixture --connection test Articles');

        $this->assertFileContains('<?php', $this->generatedFile);
        $this->assertFileContains('namespace App\Test\Fixture;', $this->generatedFile);
        $this->assertFileContains("'body' => ['type' => 'json'", $this->generatedFile);
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

        $this->generatedFile = $root . 'tests/Fixture/ArticlesFixture.php';
        $this->exec('bake fixture --connection test --plugin TestBake Articles');

        $this->assertFileContains('<?php', $this->generatedFile);
        $this->assertFileContains('namespace TestBake\Test\Fixture;', $this->generatedFile);
        $this->assertFileContains('class ArticlesFixture', $this->generatedFile);
    }
}
