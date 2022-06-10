<?php
declare(strict_types=1);

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
 * @since         2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Command;

use Bake\Test\TestCase\TestCase;
use Bake\Utility\SubsetSchemaCollection;
use Cake\Console\CommandInterface;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;

/**
 * TemplateAllCommand test
 */
class TemplateAllCommandTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'plugin.Bake.Articles',
        'plugin.Bake.Comments',
    ];

    /**
     * @var array<string>
     */
    protected $tables = ['articles', 'comments'];

    /**
     * setUp method
     *
     * Ensure that the default template is used
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Template' . DS;

        $this->setAppNamespace('Bake\Test\App');
        $this->useCommandRunner();

        $connection = ConnectionManager::get('test');
        $subsetCollection = new SubsetSchemaCollection($connection->getSchemaCollection(), $this->tables);
        $connection->setSchemaCollection($subsetCollection);
    }

    /**
     * tearDown method
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
     * Test execute
     *
     * @return void
     */
    public function testExecuteSimple()
    {
        $this->generatedFiles = [
            ROOT . 'templates/Articles/add.php',
            ROOT . 'templates/Articles/edit.php',
            ROOT . 'templates/Articles/index.php',
            ROOT . 'templates/Articles/view.php',
            ROOT . 'templates/Comments/add.php',
            ROOT . 'templates/Comments/edit.php',
            ROOT . 'templates/Comments/index.php',
            ROOT . 'templates/Comments/view.php',
        ];
        $this->exec('bake template all');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
    }

    /**
     * Test execute
     *
     * @return void
     */
    public function testExecuteOptionForwarding()
    {
        $this->generatedFiles = [
            ROOT . 'templates/Articles/index.php',
            ROOT . 'templates/Articles/add.php',
            ROOT . 'templates/Articles/edit.php',
            ROOT . 'templates/Articles/view.php',
            ROOT . 'templates/Comments/index.php',
            ROOT . 'templates/Comments/add.php',
            ROOT . 'templates/Comments/edit.php',
            ROOT . 'templates/Comments/view.php',
        ];
        $this->exec('bake template all --index-columns 3');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains('title', $this->generatedFiles[0]);
        $this->assertFileNotContains('published', $this->generatedFiles[0]);
    }
}
