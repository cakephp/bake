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
namespace Bake\Test\TestCase\Command;

use Bake\Test\TestCase\TestCase;
use Bake\Utility\SubsetSchemaCollection;
use Cake\Console\CommandInterface;
use Cake\Datasource\ConnectionManager;

/**
 * AllCommand Test
 */
class AllCommandTest extends TestCase
{
    /**
     * fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'plugin.Bake.Products',
        'plugin.Bake.ProductVersions',
    ];

    /**
     * @var array<string>
     */
    protected array $tables = ['products', 'product_versions'];

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

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
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

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertOutputContains('Bake All complete');
    }

    /**
     * Tests execute() generating a full stack with prefixes
     *
     * @return void
     */
    public function testExecuteWithPrefix()
    {
        $path = APP;
        $testsPath = ROOT . 'tests' . DS;
        $templatesPath = ROOT . 'templates' . DS;

        $this->generatedFiles = [
            $templatesPath . 'Admin/Products/add.php',
            $templatesPath . 'Admin/Products/edit.php',
            $templatesPath . 'Admin/Products/index.php',
            $templatesPath . 'Admin/Products/view.php',
            $path . 'Controller/Admin/ProductsController.php',
            $path . 'Model/Table/ProductsTable.php',
            $path . 'Model/Entity/Product.php',
            $testsPath . 'Fixture/ProductsFixture.php',
            $testsPath . 'TestCase/Model/Table/ProductsTableTest.php',
            $testsPath . 'TestCase/Controller/Admin/ProductsControllerTest.php',
        ];
        $this->exec('bake all --connection test --prefix admin Products', ['y']);

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains(
            'namespace Bake\Test\App\Controller\Admin;',
            $path . 'Controller/Admin/ProductsController.php'
        );
        $this->assertFileContains(
            'use Bake\Test\App\Controller\AppController;',
            $path . 'Controller/Admin/ProductsController.php'
        );
        $this->assertFileContains(
            'class ProductsController extends AppController',
            $path . 'Controller/Admin/ProductsController.php'
        );
        $this->assertFileContains(
            'use Bake\Test\App\Controller\Admin\ProductsController;',
            $testsPath . 'TestCase/Controller/Admin/ProductsControllerTest.php'
        );
        $this->assertOutputContains('Bake All complete');
    }

    /**
     * Test docblock @ uses generated for test methods
     *
     * @return void
     */
    public function testGenerateUsesDocBlockController()
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
        $this->exec('bake all --connection test Products', ['y', 'y', 'y', 'y']);

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileContains(
            '@uses \Bake\Test\App\Controller\ProductsController::index()',
            $testsPath . 'TestCase/Controller/ProductsControllerTest.php'
        );
        $this->assertFileContains(
            '@uses \Bake\Test\App\Model\Table\ProductsTable::validationDefault()',
            $testsPath . 'TestCase/Model/Table/ProductsTableTest.php'
        );
        $this->assertOutputContains('Bake All complete');
        foreach ($this->generatedFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
