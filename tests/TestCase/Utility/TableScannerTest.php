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
 * @since         2.0.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Utility;

use Bake\Test\TestCase\TestCase;
use Bake\Utility\TableScanner;
use Cake\Datasource\ConnectionManager;

class TableScannerTest extends TestCase
{
    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'plugin.Bake.TodoTasks',
        'plugin.Bake.TodoItems',
    ];

    /**
     * @var \Bake\Utility\TableScanner
     */
    protected $tableScanner;

    /**
     * @var \Cake\Database\Connection
     */
    protected $connection;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->connection = ConnectionManager::get('test');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->tableScanner);
    }

    /**
     * @return void
     */
    public function testListAll()
    {
        $this->tableScanner = new TableScanner($this->connection);

        $result = $this->tableScanner->listAll();
        $list = [
            'todo_items' => true,
            'todo_tasks' => true,
        ];
        foreach ($list as $key => $expected) {
            if ($expected) {
                $this->assertArrayHasKey($key, $result);
            } else {
                $this->assertArrayNotHasKey($key, $result);
            }
        }
    }

    /**
     * @return void
     */
    public function testListUnskipped()
    {
        $this->tableScanner = new TableScanner($this->connection, ['todo_items']);

        $result = $this->tableScanner->listUnskipped();
        $list = [
            'todo_items' => false,
            'todo_tasks' => true,
        ];
        foreach ($list as $key => $expected) {
            if ($expected) {
                $this->assertArrayHasKey($key, $result);
            } else {
                $this->assertArrayNotHasKey($key, $result);
            }
        }
    }

    /**
     * @return void
     */
    public function testListUnskippedRegex()
    {
        $this->tableScanner = new TableScanner($this->connection, ['/tasks$/']);

        $result = $this->tableScanner->listUnskipped();
        $list = [
            'todo_items' => true,
            'todo_tasks' => false,
        ];
        foreach ($list as $key => $expected) {
            if ($expected) {
                $this->assertArrayHasKey($key, $result);
            } else {
                $this->assertArrayNotHasKey($key, $result);
            }
        }
    }
}
