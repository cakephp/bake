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
 * @since         1.1.4
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Command;

use Bake\Test\TestCase\TestCase;
use Cake\Console\CommandInterface;
use Cake\Core\Plugin;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlite;
use Cake\Database\Driver\Sqlserver;
use Cake\Datasource\ConnectionManager;

/**
 * ModelCommand Association detection test
 */
class ModelCommandAssociationDetectionTest extends TestCase
{
    /**
     * fixtures
     *
     * Don't sort this list alphabetically - otherwise there are table constraints
     * which fail when using postgres
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'plugin.Bake.Categories',
        'plugin.Bake.CategoriesProducts',
        'plugin.Bake.OldProducts',
        'plugin.Bake.Products',
        'plugin.Bake.ProductVersions',
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

        $this->getTableLocator()->clear();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->getTableLocator()->clear();
    }

    /**
     * Compare bake table result with static comparison file
     *
     * @return void
     */
    protected function _compareBakeTableResult($name, $comparisonFile)
    {
        $this->generatedFiles = [
            APP . "Model/Table/{$name}Table.php",
        ];
        $this->exec("bake model --no-entity --no-fixture --no-test --connection test {$name}");

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $contents = file_get_contents($this->generatedFiles[0]);
        $this->assertSameAsFile($comparisonFile . '.php', $contents);
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
        $this->skipIf($driver instanceof Sqlite, 'Incompatible with sqlite');
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
        $this->skipIf($driver instanceof Postgres, 'Incompatible with postgres');
        $this->skipIf($driver instanceof Sqlserver, 'Incompatible with sqlserver');
        $this->_compareBakeTableResult('ProductVersions', __FUNCTION__);
    }
}
