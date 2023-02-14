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

use Bake\Command\ControllerCommand;
use Bake\Test\App\Model\Table\BakeArticlesTable;
use Bake\Test\TestCase\TestCase;
use Cake\Console\Arguments;
use Cake\Console\CommandInterface;
use Cake\Core\Plugin;

/**
 * ControllerCommand Test
 */
class ControllerCommandTest extends TestCase
{
    /**
     * fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'plugin.Bake.BakeArticles',
        'plugin.Bake.BakeArticlesBakeTags',
        'plugin.Bake.BakeComments',
        'plugin.Bake.BakeTags',
    ];

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

        $this->removePlugins(['ControllerTest', 'Company/Pastry']);
    }

    /**
     * test main listing available models.
     *
     * @return void
     */
    public function testMainListAvailable()
    {
        $this->exec('bake controller');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertOutputContains('- BakeArticles');
        $this->assertOutputContains('- BakeArticlesBakeTags');
        $this->assertOutputContains('- BakeComments');
        $this->assertOutputContains('- BakeTags');
    }

    /**
     * test component generation
     *
     * @return void
     */
    public function testGetComponents()
    {
        $command = new ControllerCommand();
        $args = new Arguments([], [], []);
        $result = $command->getComponents($args);
        $this->assertSame([], $result);

        $args = new Arguments([], ['components' => '  , Auth, ,  RequestHandler'], []);
        $result = $command->getComponents($args);
        $this->assertSame(['Auth', 'RequestHandler'], $result);
    }

    /**
     * test helper generation
     *
     * @return void
     */
    public function testGetHelpers()
    {
        $command = new ControllerCommand();
        $args = new Arguments([], [], []);
        $result = $command->getHelpers($args);
        $this->assertSame([], $result);

        $args = new Arguments([], ['helpers' => '  , Session , ,  Number'], []);
        $result = $command->getHelpers($args);
        $this->assertSame(['Session', 'Number'], $result);
    }

    /**
     * test bake with various component name variants
     *
     * @return void
     */
    public function testBakeComponents()
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec(
            'bake controller --connection test --no-test --no-actions ' .
            '--components "FormProtection, Flash, Company/TestBakeThree.Something, TestBake.Other, Apple, NonExistent" ' .
            'BakeArticles'
        );

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test the bake method
     *
     * @return void
     */
    public function testBakeActionsOption()
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec(
            'bake controller --connection test --no-test ' .
            '--helpers Html,Time --components FormProtection,Flash ' .
            '--actions index BakeArticles'
        );

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test the bake method
     *
     * @return void
     */
    public function testBakeNoActions()
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec(
            'bake controller --connection test --no-test ' .
            '--helpers Html,Time --components FormProtection,Flash --no-actions BakeArticles'
        );

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test bake with actions.
     *
     * @return void
     */
    public function testBakeActions()
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec(
            'bake controller --connection test --no-test ' .
            '--helpers Html,Time --components "FormProtection, Flash" BakeArticles'
        );

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test bake actions prefixed.
     *
     * @return void
     */
    public function testBakePrefixed()
    {
        $this->generatedFile = APP . 'Controller/Admin/BakeArticlesController.php';
        $this->exec('bake controller --connection test --no-test --prefix admin BakeArticles');

        $this->assertFileContains('namespace Bake\Test\App\Controller\Admin;', $this->generatedFile);
        $this->assertFileContains('use Bake\Test\App\Controller\AppController;', $this->generatedFile);
        $this->assertFileContains('class BakeArticlesController extends', $this->generatedFile);
    }

    /**
     * test bake actions with nested prefixes.
     *
     * @return void
     */
    public function testBakePrefixNested()
    {
        $this->generatedFile = APP . 'Controller/Admin/Management/BakeArticlesController.php';
        $this->exec('bake controller --connection test --no-test --prefix admin/management BakeArticles');

        $this->assertFileContains('namespace Bake\Test\App\Controller\Admin\Management;', $this->generatedFile);
        $this->assertFileContains('use Bake\Test\App\Controller\AppController;', $this->generatedFile);
        $this->assertFileContains('class BakeArticlesController extends', $this->generatedFile);
    }

    /**
     * test bake() with a -plugin param
     *
     * @return void
     */
    public function testBakeWithPlugin()
    {
        $this->_loadTestPlugin('BakeTest');
        $path = Plugin::path('BakeTest');

        $this->generatedFile = $path . 'src/Controller/BakeArticlesController.php';
        $this->exec('bake controller --connection test --no-test BakeTest.BakeArticles');

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test that bakeActions is creating the correct controller Code. (Using sessions)
     *
     * @return void
     */
    public function testBakeActionsContent()
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec('bake controller --connection test --no-test BakeArticles');

        $result = file_get_contents($this->generatedFile);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test baking a test
     *
     * @return void
     */
    public function testBakeTest()
    {
        $this->generatedFiles = [
            APP . 'Controller/BakeArticlesController.php',
            ROOT . 'tests/TestCase/Controller/BakeArticlesControllerTest.php',
        ];
        $this->exec('bake controller --connection test BakeArticles');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains(
            'class BakeArticlesControllerTest extends TestCase',
            $this->generatedFiles[1]
        );
        $this->assertFileContains(
            'use IntegrationTestTrait',
            $this->generatedFiles[1]
        );
    }

    /**
     * test baking a test
     *
     * @return void
     */
    public function testBakeTestDisabled()
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec('bake controller --connection test --no-test BakeArticles');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileDoesNotExist(ROOT . 'tests/TestCase/Controller/BakeArticlesControllerTest.php');
        $this->assertFileExists($this->generatedFile);
    }

    /**
     * Test execute no args.
     *
     * @return void
     */
    public function testMainNoArgs()
    {
        $this->exec('bake controller');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertOutputContains('Possible controllers based on your current database');
        $this->assertOutputContains('- BakeArticles');
    }

    /**
     * data provider for testMainWithControllerNameVariations
     *
     * @return void
     */
    public static function nameVariations()
    {
        return [
            ['BakeArticles'], ['bake_articles'],
        ];
    }

    /**
     * test that both plural and singular forms work for controller baking.
     *
     * @dataProvider nameVariations
     * @return void
     */
    public function testMainWithControllerNameVariations($name)
    {
        $this->generatedFile = APP . 'Controller/BakeArticlesController.php';
        $this->exec("bake controller --connection test --no-test {$name}");
        $this->assertExitCode(CommandInterface::CODE_SUCCESS);

        $this->assertFileExists($this->generatedFile);
        $this->assertFileContains('BakeArticlesController extends AppController', $this->generatedFile);
    }

    /**
     * test main with plugin.name
     *
     * @return void
     */
    public function testMainWithPluginDot()
    {
        $this->_loadTestPlugin('Company/Pastry');
        $path = Plugin::path('Company/Pastry');

        $this->generatedFile = $path . 'src/Controller/BakeArticlesController.php';

        $this->exec('bake controller --connection test --no-test Company/Pastry.BakeArticles');
        $this->assertExitCode(CommandInterface::CODE_SUCCESS);

        $this->assertFileExists($this->generatedFile);
        $this->assertFileContains('namespace Company\Pastry\Controller;', $this->generatedFile);
        $this->assertFileContains('use Company\Pastry\Controller\AppController;', $this->generatedFile);
        $this->assertFileContains('BakeArticlesController extends AppController', $this->generatedFile);
    }

    /**
     * test main with plugin.name
     *
     * @return void
     */
    public function testMainWithPluginOption()
    {
        $this->_loadTestPlugin('Company/Pastry');
        $path = Plugin::path('Company/Pastry');

        $this->generatedFile = $path . 'src/Controller/BakeArticlesController.php';

        $this->exec('bake controller --connection test --no-test --plugin Company/Pastry bake_articles');
        $this->assertExitCode(CommandInterface::CODE_SUCCESS);

        $this->assertFileExists($this->generatedFile);
        $this->assertFileContains('namespace Company\Pastry\Controller;', $this->generatedFile);
        $this->assertFileContains('use Company\Pastry\Controller\AppController;', $this->generatedFile);
        $this->assertFileContains('BakeArticlesController extends AppController', $this->generatedFile);
    }
}
