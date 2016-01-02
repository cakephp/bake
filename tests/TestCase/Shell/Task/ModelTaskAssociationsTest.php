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
use Cake\Model\Model;
use Cake\ORM\TableRegistry;
use Cake\Utility\ClassRegistry;

/**
 * ModelTaskAssociationsTest class
 */
class ModelTaskAssociationsTest extends TestCase
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
        'plugin.bake.categories',
        'plugin.bake.categories_products',
        'plugin.bake.old_products',
        'plugin.bake.products',
        'plugin.bake.product_versions',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Model' . DS;
        $io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Task = $this->getMock(
            'Bake\Shell\Task\ModelTask',
            ['in', 'err', 'createFile', '_stop', '_checkUnitTest'],
            [$io]
        );
        $this->Task->connection = 'default';
        $this->_setupOtherMocks();
        TableRegistry::clear();
    }

    /**
     * Setup a mock that has out mocked. Normally this is not used as it makes $this->at() really tricky.
     *
     * @return void
     */
    protected function _useMockedOut()
    {
        $io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Task = $this->getMock(
            'Bake\Shell\Task\ModelTask',
            ['in', 'out', 'err', 'hr', 'createFile', '_stop', '_checkUnitTest'],
            [$io]
        );
        $this->_setupOtherMocks();
    }

    /**
     * sets up the rest of the dependencies for Model Task
     *
     * @return void
     */
    protected function _setupOtherMocks()
    {
        $io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Task->Fixture = $this->getMock('Bake\Shell\Task\FixtureTask', [], [$io]);
        $this->Task->Test = $this->getMock('Bake\Shell\Task\FixtureTask', [], [$io]);
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
     * test checking if associations where built correctly for categories.
     *
     * @return void
     */
    public function testBakeAssociationDetectionCategoriesTable()
    {
        $name = 'Categories';
        $table = $this->Task->getTable($name);
        $tableObject = $this->Task->getTableObject($name, $table);
        $data = $this->Task->getTableContext($name);
        $result = $this->Task->bakeTable($tableObject, $data);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }
    
    /**
     * test checking if associations where built correctly for categories_products.
     *
     * @return void
     */
    public function testBakeAssociationDetectionCategoriesProductsTable()
    {
        $name = 'CategoriesProducts';
        $table = $this->Task->getTable($name);
        $tableObject = $this->Task->getTableObject($name, $table);
        $data = $this->Task->getTableContext($name);
        $result = $this->Task->bakeTable($tableObject, $data);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }
    
    /**
     * test checking if associations where built correctly for old_products.
     *
     * @return void
     */
    public function testBakeAssociationDetectionOldProductsTable()
    {
        $name = 'OldProducts';
        $table = $this->Task->getTable($name);
        $tableObject = $this->Task->getTableObject($name, $table);
        $data = $this->Task->getTableContext($name);
        $result = $this->Task->bakeTable($tableObject, $data);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }
    
    /**
     * test checking if associations where built correctly for product_versions.
     *
     * @return void
     */
    public function testBakeAssociationDetectionProductVersionsTable()
    {
        $name = 'ProductVersions';
        $table = $this->Task->getTable($name);
        $tableObject = $this->Task->getTableObject($name, $table);
        $data = $this->Task->getTableContext($name);
        $result = $this->Task->bakeTable($tableObject, $data);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }
    
    /**
     * test checking if associations where built correctly for products.
     *
     * @return void
     */
    public function testBakeAssociationDetectionProductsTable()
    {
        $name = 'Products';
        $table = $this->Task->getTable($name);
        $tableObject = $this->Task->getTableObject($name, $table);
        $data = $this->Task->getTableContext($name);
        $result = $this->Task->bakeTable($tableObject, $data);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }
}
