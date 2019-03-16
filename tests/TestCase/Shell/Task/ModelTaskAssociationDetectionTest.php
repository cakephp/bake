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
 * @since         1.1.4
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Shell\Task;

use Bake\Shell\Task\BakeTemplateTask;
use Bake\Test\TestCase\TestCase;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlite;
use Cake\Database\Driver\Sqlserver;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

/**
 * ModelTaskAssociationDetectionTest class
 */
class ModelTaskAssociationDetectionTest extends TestCase
{
    /**
     * fixtures
     *
     * Don't sort this list alphabetically - otherwise there are table constraints
     * which fail when using postgres
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Bake.Categories',
        'plugin.Bake.CategoriesProducts',
        'plugin.Bake.OldProducts',
        'plugin.Bake.Products',
        'plugin.Bake.ProductVersions',
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

        $this->Task->connection = 'default';
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
            ->setMethods(['bake'])
            ->getMock();
        $this->Task->Test = $this->getMockBuilder('Bake\Shell\Task\FixtureTask')
            ->setConstructorArgs([$io])
            ->setMethods(['bake'])
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
     * Compare bake table result with static comparison file
     *
     * @return void
     */
    protected function _compareBakeTableResult($name, $comparisonFile)
    {
        $table = $this->Task->getTable($name);
        $tableObject = $this->Task->getTableObject($name, $table);
        $data = $this->Task->getTableContext($tableObject, $table, $name);
        $result = $this->Task->bakeTable($tableObject, $data);
        $this->assertSameAsFile($comparisonFile . '.php', $result);
    }

    /**
     * test checking if associations where built correctly for categories.
     *
     * @return void
     */
    public function testBakeAssociationDetectionCategoriesTable()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Mysql, 'Incompatible with mysql');
        $this->_compareBakeTableResult('Categories', __FUNCTION__);
    }

    /**
     * test checking if associations where built correctly for categories.
     *
     * @return void
     */
    public function testBakeAssociationDetectionCategoriesTableSigned()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Sqlite, 'Incompatible with sqlite');
        $this->skipIf($driver instanceof Postgres, 'Incompatible with postgres');
        $this->skipIf($driver instanceof Sqlserver, 'Incompatible with sqlserver');
        $this->_compareBakeTableResult('Categories', __FUNCTION__);
    }

    /**
     * test checking if associations where built correctly for categories_products.
     *
     * @return void
     */
    public function testBakeAssociationDetectionCategoriesProductsTable()
    {
        $this->_compareBakeTableResult('CategoriesProducts', __FUNCTION__);
    }

    /**
     * test checking if associations where built correctly for old_products.
     *
     * @return void
     */
    public function testBakeAssociationDetectionOldProductsTable()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Mysql, 'Incompatible with mysql');
        $this->_compareBakeTableResult('OldProducts', __FUNCTION__);
    }

    /**
     * test checking if associations where built correctly for old_products.
     *
     * @return void
     */
    public function testBakeAssociationDetectionOldProductsTableSigned()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Sqlite, 'Incompatible with sqlite');
        $this->skipIf($driver instanceof Postgres, 'Incompatible with postgres');
        $this->skipIf($driver instanceof Sqlserver, 'Incompatible with sqlserver');
        $this->_compareBakeTableResult('OldProducts', __FUNCTION__);
    }

    /**
     * test checking if associations where built correctly for product_versions.
     *
     * @return void
     */
    public function testBakeAssociationDetectionProductVersionsTable()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Mysql, 'Incompatible with mysql');
        $this->_compareBakeTableResult('ProductVersions', __FUNCTION__);
    }

    /**
     * test checking if associations where built correctly for product_versions.
     *
     * @return void
     */
    public function testBakeAssociationDetectionProductVersionsTableSigned()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Sqlite, 'Incompatible with sqlite');
        $this->skipIf($driver instanceof Postgres, 'Incompatible with postgres');
        $this->skipIf($driver instanceof Sqlserver, 'Incompatible with sqlserver');
        $this->_compareBakeTableResult('ProductVersions', __FUNCTION__);
    }

    /**
     * test checking if associations where built correctly for products.
     *
     * @return void
     */
    public function testBakeAssociationDetectionProductsTable()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Mysql, 'Incompatible with mysql');
        $this->_compareBakeTableResult('Products', __FUNCTION__);
    }

    /**
     * test checking if associations where built correctly for products.
     *
     * @return void
     */
    public function testBakeAssociationDetectionProductsTableSigned()
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->skipIf($driver instanceof Sqlite, 'Incompatible with sqlite');
        $this->skipIf($driver instanceof Postgres, 'Incompatible with postgres');
        $this->skipIf($driver instanceof Sqlserver, 'Incompatible with sqlserver');
        $this->_compareBakeTableResult('Products', __FUNCTION__);
    }
}
