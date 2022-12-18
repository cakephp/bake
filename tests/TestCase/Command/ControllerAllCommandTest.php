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

use Bake\Test\App\Model\Table\BakeArticlesTable;
use Bake\Test\TestCase\TestCase;
use Cake\Console\CommandInterface;
use Cake\Core\Plugin;
use Cake\Utility\Inflector;

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
    public function setUp(): void
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'Controller' . DS;
        $this->setAppNamespace('Bake\Test\App');

        $this->getTableLocator()->get('BakeArticles', [
            'className' => BakeArticlesTable::class,
        ]);
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
     * test that execute runs all when the first arg == all
     *
     * @return void
     */
    public function testExecute()
    {
        foreach ($this->tables as $table) {
            $plural = Inflector::camelize($table);

            $this->generatedFiles[] = APP . "Controller/{$plural}Controller.php";
        }
        $this->exec('bake controller all --connection test --no-test --quiet');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);

        $this->assertFileDoesNotExist(
            ROOT . 'tests/TestCase/Controller/BakeArticlesControllerTest.php',
            'Test should not be created as options should be forwarded'
        );
    }
}
