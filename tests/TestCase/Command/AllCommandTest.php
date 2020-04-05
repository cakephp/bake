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
use Cake\Command\Command;
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
    protected $fixtures = [
        'plugin.Bake.Products',
        'plugin.Bake.ProductVersions',
    ];

    /**
     * @var array
     */
    protected $tables = ['products', 'product_versions'];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
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
    public function tearDown(): void
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
            $templatesPath . 'Products/add.php',
            $templatesPath . 'Products/edit.php',
            $templatesPath . 'Products/index.php',
            $templatesPath . 'Products/view.php',
            $path . 'Controller/ProductsController.php',
            $path . 'Model/Table/ProductsTable.php',
            $path . 'Model/Entity/Product.php',
            $testsPath . 'Fixture/ProductsFixture.php',
            $testsPath . 'TestCase/Model/Table/ProductsTableTest.php',
            $testsPath . 'TestCase/Controller/ProductsControllerTest.php',
        ];
        $this->exec('bake all --connection test Products', ['y']);

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
            $templatesPath . 'Products/add.php',
            $templatesPath . 'Products/edit.php',
            $templatesPath . 'Products/index.php',
            $templatesPath . 'Products/view.php',
            $path . 'Controller/ProductsController.php',
            $path . 'Model/Table/ProductsTable.php',
            $path . 'Model/Entity/Product.php',
            $testsPath . 'Fixture/ProductsFixture.php',
            $testsPath . 'TestCase/Model/Table/ProductsTableTest.php',
            $testsPath . 'TestCase/Controller/ProductsControllerTest.php',

            $templatesPath . 'ProductVersions/add.php',
            $templatesPath . 'ProductVersions/edit.php',
            $templatesPath . 'ProductVersions/index.php',
            $templatesPath . 'ProductVersions/view.php',
            $path . 'Controller/ProductVersionsController.php',
            $path . 'Model/Table/ProductVersionsTable.php',
            $path . 'Model/Entity/ProductVersion.php',
            $testsPath . 'Fixture/ProductVersionsFixture.php',
            $testsPath . 'TestCase/Model/Table/ProductVersionsTableTest.php',
            $testsPath . 'TestCase/Controller/ProductVersionsControllerTest.php',
        ];
        $this->exec('bake all --connection test --everything', ['y']);

        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertOutputContains('Bake All complete');
    }
}
