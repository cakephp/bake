<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Command;

use Bake\Command\FixtureCommand;
use Bake\Test\TestCase\TestCase;
use Cake\Command\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\Exception\StopException;
use Cake\Console\TestSuite\StubConsoleInput;
use Cake\Console\TestSuite\StubConsoleOutput;
use Cake\Core\Plugin;
use Cake\Database\Driver\Postgres;
use Cake\Datasource\ConnectionManager;

/**
 * FixtureCommand Test
 */
class FixtureCommandTest extends TestCase
{
    /**
     * fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'plugin.Bake.Articles',
        'plugin.Bake.Comments',
        'plugin.Bake.Datatypes',
        'plugin.Bake.BinaryTests',
        'plugin.Bake.BakeCar',
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

        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Fixture' . DS;
        $this->setAppNamespace('Bake\Test\App');
    }

    /**
     * Tests validating supported table and column names.
     */
    public function testValidateNamesWithValid(): void
    {
        $command = new FixtureCommand();
        $command->connection = 'test';

        $schema = $command->readSchema('Car', 'car');
        $schema->addColumn('_valid', ['type' => 'string', 'length' => null]);

        $abortCalled = false;
        try {
            $io = new ConsoleIo(new StubConsoleOutput(), new StubConsoleOutput(), new StubConsoleInput([]));
            $command->validateNames($schema, $io);
        } catch (StopException) {
            $abortCalled = true;
        }
        $this->assertFalse($abortCalled);
    }

    /**
     * Tests validating supported table and column names.
     */
    public function testValidateNamesWithInvalid(): void
    {
        $command = new FixtureCommand();
        $command->connection = 'test';

        $schema = $command->readSchema('Car', 'car');
        $schema->addColumn('0invalid', ['type' => 'string', 'length' => null]);

        $this->expectException(StopException::class);
        $io = new ConsoleIo(new StubConsoleOutput(), new StubConsoleOutput(), new StubConsoleInput([]));
        $command->validateNames($schema, $io);
    }

    /**
     * Tests validating supported table and column names.
     */
    public function testValidateNamesWithInvalidSpecialChars(): void
    {
        $command = new FixtureCommand();
        $command->connection = 'test';

        $schema = $command->readSchema('Car', 'car');
        $schema->addColumn('invalid:column', ['type' => 'string', 'length' => null]);

        $this->expectException(StopException::class);
        $io = new ConsoleIo(new StubConsoleOutput(), new StubConsoleOutput(), new StubConsoleInput([]));
        $command->validateNames($schema, $io);
    }

    /**
     * test generating a fixture with database rows.
     *
     * @return void
     */
    public function testImportRecordsFromDatabase()
    {
        $this->generatedFile = ROOT . 'tests/Fixture/DatatypesFixture.php';
        $this->exec('bake fixture --connection test --schema --records --count 2 Datatypes');
        $this->assertExitSuccess();

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
        $this->assertExitCode(Command::CODE_SUCCESS);

        $this->assertStringContainsString("'connection' => 'test'", file_get_contents($this->generatedFile));
    }

    /**
     * Ensure that fixture data doesn't get overly escaped.
     *
     * @return void
     */
    public function testImportRecordsNoEscaping()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $articles->updateAll(['body' => 'Body "value"'], []);

        $this->generatedFile = ROOT . 'tests/Fixture/ArticleFixture.php';
        $this->exec('bake fixture --connection test --schema --records Article');

        $this->assertExitCode(Command::CODE_SUCCESS);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFileContains("public string \$table = 'comments';", $this->generatedFile);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFileContains('class CarFixture ', $this->generatedFile);
        $this->assertFileContains('$this->records', $this->generatedFile);
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

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFileContains('class ArticlesFixture', $this->generatedFile);
    }

    /**
     * test interactive mode of execute
     *
     * @return void
     */
    public function testMainNoArgs()
    {
        $this->exec('bake fixture --connection test');

        $this->assertExitCode(Command::CODE_SUCCESS);
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
        $this->exec('bake fixture --connection test --fields Articles');

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFileContains('class ArticlesFixture extends TestFixture', $this->generatedFile);
        $this->assertFileContains('$this->records =', $this->generatedFile);
        $this->assertFileNotContains('public array $import', $this->generatedFile);
    }

    /**
     * Test no fields by default
     *
     * @return void
     */
    public function testBakeNoFields()
    {
        $this->generatedFile = ROOT . 'tests/Fixture/ArticlesFixture.php';
        $this->exec('bake fixture --connection test Articles');

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFileContains('class ArticlesFixture extends TestFixture', $this->generatedFile);
        $this->assertFileNotContains('public $fields', $this->generatedFile);
        $this->assertFileNotContains('public $import', $this->generatedFile);
        $this->assertFileContains('$this->records =', $this->generatedFile);
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

        $importString = "public array \$import = ['table' => 'comments', 'connection' => 'test'];";
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
        $this->exec('bake fixture --connection test --fields Datatypes');

        $this->assertFileExists($this->generatedFile);
        $result = file_get_contents($this->generatedFile);
        $this->assertStringContainsString("'float_field' => 1", $result);
        $this->assertStringContainsString("'decimal_field' => 1.5", $result);
        $this->assertStringContainsString("'huge_int' => 1", $result);
        $this->assertStringContainsString("'small_int' => 1", $result);
        $this->assertStringContainsString("'tiny_int' => 1", $result);
        $this->assertStringContainsString("'bool' => 1", $result);
        $this->assertStringContainsString('_constraints', $result);
        $this->assertStringContainsString("'primary' => ['type' => 'primary'", $result);
        $this->assertStringContainsString("'columns' => ['id']", $result);
        $this->assertStringContainsString("'uuid' => ['type' => 'uuid'", $result);
        $this->assertMatchesRegularExpression(
            "/(\s+)('uuid' => ')([a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89aAbB][a-f0-9]{3}-[a-f0-9]{12})(')/",
            $result
        );
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
        $table = $this->getTableLocator()->get('Articles');
        $table->getSchema()->addColumn('body', ['type' => 'json']);
        $this->generatedFile = ROOT . 'tests/Fixture/ArticlesFixture.php';
        $this->exec('bake fixture --connection test --fields Articles');

        $this->assertFileContains('<?php', $this->generatedFile);
        $this->assertFileContains('namespace Bake\Test\App\Test\Fixture;', $this->generatedFile);
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
