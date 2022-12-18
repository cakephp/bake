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
 * @since         2.0.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Command;

use Bake\Test\TestCase\TestCase;
use Bake\Utility\SubsetSchemaCollection;
use Cake\Console\CommandInterface;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;

/**
 * ModelAllCommand test class
 */
class ModelAllCommandTest extends TestCase
{
    /**
     * fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'plugin.Bake.TodoTasks',
        'plugin.Bake.TodoItems',
    ];

    /**
     * @var array<string>
     */
    protected array $tables = ['todo_tasks', 'todo_items'];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setAppNamespace('Bake\Test\App');

        $connection = ConnectionManager::get('test');
        $subsetCollection = new SubsetSchemaCollection($connection->getSchemaCollection(), $this->tables);
        $connection->setSchemaCollection($subsetCollection);
    }

    /**
     * teardown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        $connection = ConnectionManager::get('test');
        $connection->setSchemaCollection($connection->getSchemaCollection()->getInnerCollection());

        $this->getTableLocator()->clear();
    }

    /**
     * test execute
     *
     * @return void
     */
    public function testExecute()
    {
        foreach ($this->tables as $table) {
            $plural = Inflector::camelize($table);
            $singular = Inflector::singularize($plural);

            $this->generatedFiles[] = APP . "Model/Entity/{$singular}.php";
            $this->generatedFiles[] = ROOT . "tests/Fixture/{$plural}Fixture.php";
        }
        $this->exec('bake model all --connection test --no-table --no-test');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);

        $this->assertFileDoesNotExist(
            APP . 'Model/Table/TodoItemsTable.php',
            'Table should not be created as options should be forwarded'
        );
        $this->assertFileDoesNotExist(
            ROOT . 'tests/TestCase/Model/Table/TodoItemsTableTest.php',
            'Table test should not be created as options should be forwarded'
        );
    }
}
