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
 * @since         4.0.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Command;

use Bake\Test\App\Model\Table\BakeArticlesTable;
use Bake\Test\TestCase\TestCase;
use Cake\AttributeResolver\AttributeResolver;
use Cake\Core\Plugin;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\ServerRequest;
use Cake\Routing\Exception\MissingRouteException;
use Cake\Routing\RouteBuilder;
use Cake\Routing\RouteCollection;
use Cake\Routing\Router;
use Cake\View\Helper\FormHelper;
use Cake\View\View;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;

/**
 * Exercise routes discovered from generated controller source without fallback routes.
 */
class AttributeRoutingTest extends TestCase
{
    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'plugin.Bake.BakeArticles',
        'plugin.Bake.BakeArticlesBakeTags',
        'plugin.Bake.BakeComments',
        'plugin.Bake.BakeTags',
        'plugin.Bake.Users',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setAppNamespace('Bake\\Test\\App');
        $this->getTableLocator()->get('BakeArticles', ['className' => BakeArticlesTable::class]);
    }

    protected function tearDown(): void
    {
        AttributeResolver::drop('bake-routing-test');
        $this->getTableLocator()->clear();
        $this->removePlugins(['BakeTest', 'Company/Pastry', 'Authentication']);
        parent::tearDown();
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function conflictingOptionsProvider(): array
    {
        return [
            'controller' => ['controller BakeArticles', APP . 'Controller/BakeArticlesController.php'],
            'bulk controllers' => ['controller all', APP . 'Controller/BakeArticlesController.php'],
            'all' => ['all RoutingExamples', APP . 'Model/Table/RoutingExamplesTable.php'],
            'plugin' => ['plugin RoutingExample', TMP . 'plugins/RoutingExample/src/RoutingExamplePlugin.php'],
        ];
    }

    #[DataProvider('conflictingOptionsProvider')]
    public function testConflictingOptionsDoNotGenerateFiles(string $command, string $path): void
    {
        $this->exec('bake ' . $command . ' --attribute-routing --no-attribute-routing');

        $this->assertExitError();
        $this->assertErrorContains('cannot be used together');
        $this->assertFileDoesNotExist($path);
        $this->assertOutputNotContains('Creating file');
    }

    public function testGeneratedRoutes(): void
    {
        $this->loadPlugins([
            'BakeTest' => ['path' => ROOT . 'Plugin/BakeTest/'],
            'Company/Pastry' => ['path' => ROOT . 'Plugin/Company/Pastry/'],
        ]);
        $controllers = [
            ['', 'bake-articles', null, null],
            ['--prefix admin/management', 'admin/management/bake-articles', null, 'Admin/Management'],
            ['--plugin BakeTest', 'bake-test/bake-articles', 'BakeTest', null],
            ['--plugin BakeTest --prefix admin', 'bake-test/admin/bake-articles', 'BakeTest', 'Admin'],
            ['--plugin Company/Pastry --prefix admin/management',
                'company/pastry/admin/management/bake-articles', 'Company/Pastry', 'Admin/Management'],
        ];
        foreach ($controllers as [$options, , $plugin, $prefix]) {
            $path = $plugin ? Plugin::path($plugin) . 'src/' : APP;
            $path .= 'Controller/' . ($prefix ? $prefix . '/' : '') . 'BakeArticlesController.php';
            $this->generatedFiles[] = $path;
            $this->exec('bake controller --connection test --no-test ' . $options . ' BakeArticles');
            $this->assertExitSuccess();
        }

        $collection = $this->connectGeneratedRoutes();
        $actions = [
            ['index', '', []],
            ['index', '/index', []],
            ['view', '/view/a1b2-uuid', ['a1b2-uuid']],
            ['add', '/add', []],
            ['edit', '/edit/42', ['42']],
            ['delete', '/delete/42', ['42']],
        ];
        foreach ($controllers as [, $base, $plugin, $prefix]) {
            foreach ($actions as [$action, $suffix, $pass]) {
                foreach (['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'] as $method) {
                    $result = $collection->parseRequest(new ServerRequest([
                        'url' => '/' . $base . $suffix,
                        'environment' => ['REQUEST_METHOD' => $method],
                    ]));
                    $this->assertSame('BakeArticles', $result['controller']);
                    $this->assertSame($action, $result['action']);
                    $this->assertSame($plugin, $result['plugin']);
                    $this->assertSame($prefix, $result['prefix'] ?? null);
                    $this->assertSame($pass, $result['pass']);
                }
                $url = ['controller' => 'BakeArticles', 'action' => $action,
                    'plugin' => $plugin, 'prefix' => $prefix] + $pass;
                $expected = $action === 'index' ? $base : $base . $suffix;
                $this->assertSame($expected, $collection->match($url, []));
            }
        }
        // Routing accepts every method; the existing controller guard still rejects deletion via GET.
        $class = 'Bake\\Test\\App\\Controller\\BakeArticlesController';
        $controller = new $class(new ServerRequest(['environment' => ['REQUEST_METHOD' => 'GET']]));
        try {
            $controller->delete('42');
            $this->fail('The generated delete action must reject GET requests.');
        } catch (MethodNotAllowedException $exception) {
            $this->assertSame(405, $exception->getCode());
        }

        $request = new ServerRequest(['url' => '/bake-articles', 'params' => [
            'controller' => 'BakeArticles', 'action' => 'index', 'plugin' => null, 'prefix' => null,
        ]]);
        Router::setRequest($request);
        $form = new FormHelper(new View($request));
        foreach (['POST', 'DELETE'] as $method) {
            $link = $form->postLink('Delete', [
                'action' => 'delete', '42',
            ], ['method' => strtolower($method)]);
            $this->assertStringContainsString('action="/bake-articles/delete/42"', $link);
            $this->assertStringContainsString('value="' . $method . '"', $link);
        }
        $edit = $form->create(null, [
            'url' => ['action' => 'edit', '42'],
            'type' => 'put',
        ]);
        $this->assertStringContainsString('action="/bake-articles/edit/42"', $edit);
        $this->assertStringContainsString('value="PUT"', $edit);
        $form->end();

        foreach (
            ['GET /bake-articles/view', 'GET /bake-articles/missing'] as $request
        ) {
            [$method, $url] = explode(' ', $request);
            try {
                $collection->parseRequest(new ServerRequest([
                    'url' => $url,
                    'environment' => ['REQUEST_METHOD' => $method],
                ]));
                $this->fail('Unexpected route match for ' . $request);
            } catch (MissingRouteException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testOnlySelectedActionsAreConnected(): void
    {
        $this->generatedFile = APP . 'Controller/Selected/BakeCommentsController.php';
        $this->exec('bake controller --connection test --no-test --prefix Selected --actions view BakeComments');
        $this->assertExitSuccess();

        $collection = $this->connectGeneratedRoutes();
        $this->assertCount(1, $collection->routes());
        $result = $collection->parseRequest(new ServerRequest([
            'url' => '/selected/bake-comments/view/42',
            'environment' => ['REQUEST_METHOD' => 'GET'],
        ]));
        $this->assertSame('view', $result['action']);
        $this->assertSame(['42'], $result['pass']);
    }

    public function testAuthenticationLoginRoute(): void
    {
        $this->loadTestPlugin('Authentication');
        $this->generatedFile = APP . 'Controller/LoginRouting/UsersController.php';
        $this->exec('bake controller --connection test --no-test --prefix LoginRouting Users');
        $this->assertExitSuccess();
        $source = file_get_contents($this->generatedFile);
        $this->assertStringContainsString("allowMethod(['get', 'post'])", $source);
        $this->assertStringNotContainsString('methods:', $source);

        $collection = $this->connectGeneratedRoutes();
        foreach (['GET', 'HEAD', 'POST'] as $method) {
            $result = $collection->parseRequest(new ServerRequest([
                'url' => '/login-routing/users/login',
                'environment' => ['REQUEST_METHOD' => $method],
            ]));
            $this->assertSame('login', $result['action']);
            $this->assertSame('LoginRouting', $result['prefix']);
        }
        $class = new ReflectionClass('Bake\\Test\\App\\Controller\\LoginRouting\\UsersController');
        // Check the action guard without initializing the fixture's stub Authentication component.
        $controller = $class->newInstanceWithoutConstructor();
        foreach (['HEAD', 'PUT', 'DELETE'] as $method) {
            $controller->setRequest(new ServerRequest(['environment' => ['REQUEST_METHOD' => $method]]));
            try {
                $controller->login();
                $this->fail('The generated login action must reject ' . $method . ' requests.');
            } catch (MethodNotAllowedException $exception) {
                $this->assertSame(405, $exception->getCode());
            }
        }
    }

    protected function connectGeneratedRoutes(): RouteCollection
    {
        AttributeResolver::setConfig('bake-routing-test', [
            'paths' => ['Controller/*Controller.php', 'Controller/**/*Controller.php',
                'src/Controller/*Controller.php', 'src/Controller/**/*Controller.php'],
            'basePath' => APP,
            'cache' => false,
        ]);
        $collection = Router::getRouteCollection();
        $routes = new RouteBuilder($collection, '/');
        // The test application lives inside Bake's plugin directory. Hide that outer
        // plugin while scanning so application controllers are not classified as Bake controllers.
        $plugins = Plugin::getCollection();
        $bake = $plugins->get('Bake');
        $plugins->remove('Bake');
        try {
            $routes->connectAttributes('bake-routing-test');
        } finally {
            $plugins->add($bake);
        }

        return $collection;
    }
}
