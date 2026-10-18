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

use Bake\Command\ControllerAllCommand;
use Bake\Test\App\Model\Table\BakeArticlesTable;
use Bake\Test\TestCase\TestCase;
use Bake\Utility\SubsetSchemaCollection;
use Cake\Console\CommandInterface;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * ControllerAllCommand test
 */
class ControllerAllCommandTest extends TestCase
{
    /**
     * fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'plugin.Bake.BakeArticles',
        'plugin.Bake.BakeComments',
    ];

    /**
     * @var array<string>
     */
    protected array $tables = ['bake_articles', 'bake_comments'];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Controller' . DS;
        $this->setAppNamespace('Bake\Test\App');

        $connection = ConnectionManager::get('test');
        $subsetCollection = new SubsetSchemaCollection($connection->getSchemaCollection(), $this->tables);
        $connection->setSchemaCollection($subsetCollection);

        $this->getTableLocator()->get('BakeArticles', [
            'className' => BakeArticlesTable::class,
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = ConnectionManager::get('test');
        $connection->setSchemaCollection($connection->getSchemaCollection()->getInnerCollection());

        $this->getTableLocator()->clear();
    }

    /**
     * @return array<string, array{bool, string, bool}>
     */
    public static function attributeRoutingProvider(): array
    {
        return [
            'enabled' => [true, '', true],
            'disabled' => [false, '', false],
            'enable override' => [false, '--attribute-routing', true],
            'disable override' => [true, '--no-attribute-routing', false],
        ];
    }

    /**
     * test that execute runs all when the first arg == all
     *
     * @return void
     */
    #[DataProvider('attributeRoutingProvider')]
    public function testExecute(bool $configured, string $option, bool $enabled): void
    {
        Configure::write('Bake.attributeRouting', $configured);
        foreach ($this->tables as $table) {
            $plural = Inflector::camelize($table);

            $this->generatedFiles[] = APP . "Controller/{$plural}Controller.php";
        }
        $this->exec('bake controller all --connection test --no-test ' . $option);

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $controller = file_get_contents(APP . 'Controller/BakeArticlesController.php');
        $this->assertSame($enabled, str_contains($controller, '#[Scope('));

        $this->assertFileDoesNotExist(
            ROOT . 'tests/TestCase/Controller/BakeArticlesControllerTest.php',
            'Test should not be created as options should be forwarded',
        );
    }

    /**
     * The option parser is built before initialize() runs, so the wrapped subcommand
     * must already be available at that point. Regression test for the subcommand being
     * assigned in initialize() instead of the constructor.
     *
     * @return void
     */
    public function testGetOptionParserBeforeInitialize(): void
    {
        $command = new ControllerAllCommand();

        $this->assertInstanceOf(ConsoleOptionParser::class, $command->getOptionParser());
    }
}
