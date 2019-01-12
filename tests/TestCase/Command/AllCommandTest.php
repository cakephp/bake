<?php
declare(strict_types=1);
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
 * @since         2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Command;

use Bake\Test\TestCase\TestCase;
use Bake\Utility\SubsetSchemaCollection;
use Cake\Console\Command;
use Cake\Datasource\ConnectionManager;

/**
 * AllCommand Test
 */
class AllCommandTest extends TestCase
{
    /**
     * fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Bake.TodoItems',
        'plugin.Bake.TodoTasks',
    ];

    /**
     * @var array
     */
    protected $tables = ['todo_items', 'todo_tasks'];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->setAppNamespace('Bake\Test\App');
        $this->useCommandRunner();

        $connection = ConnectionManager::get('test');
        $subsetCollection = new SubsetSchemaCollection($connection->getSchemaCollection(), $this->tables);
        $connection->setSchemaCollection($subsetCollection);
    }

    /**
     * teardown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $connection = ConnectionManager::get('test');
        $connection->setSchemaCollection($connection->getSchemaCollection()->getInnerCollection());
    }

    /**
     * Test execute() generating a full stack
     *
     * @return void
     */
    public function testExecute()
    {
        $path = APP;
        $testsPath = ROOT . 'tests' . DS;
        $templatesPath = ROOT . 'templates' . DS;

        $this->generatedFiles = [
            $templatesPath . 'TodoItems/add.php',
            $templatesPath . 'TodoItems/edit.php',
            $templatesPath . 'TodoItems/index.php',
            $templatesPath . 'TodoItems/view.php',
            $path . 'Controller/TodoItemsController.php',
            $path . 'Model/Table/TodoItemsTable.php',
            $path . 'Model/Entity/TodoItem.php',
            $testsPath . 'Fixture/TodoItemsFixture.php',
            $testsPath . 'TestCase/Model/Table/TodoItemsTableTest.php',
            $testsPath . 'TestCase/Controller/TodoItemsControllerTest.php',
        ];
        $this->exec('bake all --connection test TodoItems');

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertOutputContains('Bake All complete');
    }

    /**
     * Test execute() generating a full stack
     *
     * @return void
     */
    public function testExecuteEverything()
    {
        $path = APP;
        $testsPath = ROOT . 'tests' . DS;
        $templatesPath = ROOT . 'templates' . DS;

        $this->generatedFiles = [
            $templatesPath . 'TodoItems/add.php',
            $templatesPath . 'TodoItems/edit.php',
            $templatesPath . 'TodoItems/index.php',
            $templatesPath . 'TodoItems/view.php',
            $templatesPath . 'TodoTasks/add.php',
            $templatesPath . 'TodoTasks/edit.php',
            $templatesPath . 'TodoTasks/index.php',
            $templatesPath . 'TodoTasks/view.php',
            $path . 'Controller/TodoItemsController.php',
            $path . 'Model/Table/TodoItemsTable.php',
            $path . 'Model/Entity/TodoItem.php',
            $path . 'Controller/TodoTasksController.php',
            $path . 'Model/Table/TodoTasksTable.php',
            $path . 'Model/Entity/TodoTask.php',
            $testsPath . 'Fixture/TodoItemsFixture.php',
            $testsPath . 'TestCase/Model/Table/TodoItemsTableTest.php',
            $testsPath . 'TestCase/Controller/TodoItemsControllerTest.php',
            $testsPath . 'Fixture/TodoTasksFixture.php',
            $testsPath . 'TestCase/Model/Table/TodoTasksTableTest.php',
            $testsPath . 'TestCase/Controller/TodoTasksControllerTest.php',
        ];
        $this->exec('bake all --connection test --everything');

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertOutputContains('Bake All complete');
    }
}
