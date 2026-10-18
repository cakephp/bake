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
namespace Bake\Test\TestCase;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Utility\Filesystem;
use PHPUnit\Framework\Attributes\DataProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Check freshly baked PHP independently of the expected-output comparison files.
 */
class GeneratedCodeTest extends TestCase
{
    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'plugin.Bake.Products',
        'plugin.Bake.ProductVersions',
    ];

    protected ?string $pluginDirectory = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setAppNamespace('Bake\\Test\\App');
    }

    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } finally {
            if ($this->pluginDirectory !== null) {
                new Filesystem()->deleteDir($this->pluginDirectory);
            }
        }
    }

    /**
     * @return array<string, array{string, string, array<string>}>
     */
    public static function simpleCommandProvider(): array
    {
        return [
            'behavior' => ['behavior', 'Model/Behavior/QualityExampleBehavior.php', []],
            'component' => ['component', 'Controller/Component/QualityExampleComponent.php', []],
            'helper' => ['helper', 'View/Helper/QualityExampleHelper.php', []],
            'form' => ['form', 'Form/QualityExampleForm.php', []],
            'middleware' => ['middleware', 'Middleware/QualityExampleMiddleware.php', []],
            'mailer' => ['mailer', 'Mailer/QualityExampleMailer.php', []],
            'command' => ['command', 'Command/QualityExampleCommand.php', []],
            'command helper' => ['command_helper', 'Command/Helper/QualityExampleHelper.php', []],
            'cell' => ['cell', 'View/Cell/QualityExampleCell.php', ['templates/cell/QualityExample/display.php']],
        ];
    }

    /**
     * @param string $command Bake command.
     * @param string $path Generated class path relative to APP.
     * @param array<string> $extraFiles Additional generated files relative to ROOT.
     * @return void
     */
    #[DataProvider('simpleCommandProvider')]
    public function testSimpleCommands(string $command, string $path, array $extraFiles): void
    {
        $this->generatedFile = APP . $path;
        foreach ($extraFiles as $extraFile) {
            $this->generatedFiles[] = ROOT . $extraFile;
        }
        $this->exec('bake ' . $command . ' QualityExample --no-test');

        $this->assertExitSuccess();
        $this->assertGeneratedCodeQuality([$this->generatedFile => 'src/' . $path]);
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function applicationProvider(): array
    {
        return [
            'attributes' => ['', true],
            'legacy' => ['--no-attribute-routing', false],
            'prefix' => ['--prefix Admin/Quality', true],
        ];
    }

    #[DataProvider('applicationProvider')]
    public function testApplication(string $options, bool $attributes): void
    {
        Configure::write('Bake.attributeRouting', $attributes);
        $prefix = str_contains($options, '--prefix') ? 'Admin/Quality/' : '';
        $files = [
            APP . 'Model/Table/ProductsTable.php' => 'src/Model/Table/ProductsTable.php',
            APP . 'Model/Entity/Product.php' => 'src/Model/Entity/Product.php',
            APP . 'Controller/' . $prefix . 'ProductsController.php' => 'src/Controller/' . $prefix . 'ProductsController.php',
            ROOT . 'tests/Fixture/ProductsFixture.php' => 'tests/Fixture/ProductsFixture.php',
            ROOT . 'tests/TestCase/Model/Table/ProductsTableTest.php' => 'tests/TestCase/Model/Table/ProductsTableTest.php',
            ROOT . 'tests/TestCase/Controller/' . $prefix . 'ProductsControllerTest.php' => 'tests/TestCase/Controller/' . $prefix . 'ProductsControllerTest.php',
        ];
        $this->generatedFiles = array_keys($files);
        foreach (['index', 'view', 'add', 'edit'] as $action) {
            $this->generatedFiles[] = ROOT . 'templates/' . $prefix . 'Products/' . $action . '.php';
        }
        $this->exec(rtrim('bake all Products --connection test ' . $options), ['y']);

        $this->assertExitSuccess();
        $this->assertGeneratedCodeQuality($files);
    }

    public function testEnum(): void
    {
        $this->generatedFile = APP . 'Model/Enum/QualityStatus.php';
        $this->exec('bake enum QualityStatus draft,published');

        $this->assertExitSuccess();
        $this->assertGeneratedCodeQuality([$this->generatedFile => 'src/Model/Enum/QualityStatus.php']);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function pluginProvider(): array
    {
        return ['attributes' => [''], 'legacy' => ['--no-attribute-routing']];
    }

    #[DataProvider('pluginProvider')]
    public function testPlugin(string $options): void
    {
        $this->pluginDirectory = TMP . 'bake_generated_quality_' . bin2hex(random_bytes(6)) . DS;
        mkdir($this->pluginDirectory, 0777, true);
        $this->exec(rtrim('bake plugin QualityCompany/QualityExample --standalone-path ' .
            $this->pluginDirectory . ' ' . $options), ['y']);

        $this->assertExitSuccess();
        $root = $this->pluginDirectory . 'QualityCompany/QualityExample/';
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
        $generated = [];
        foreach ($files as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $path = $file->getPathname();
            $this->assertPhpLints($path);
            $generated[$path] = substr($path, strlen($root));
        }
        $this->assertNotEmpty($generated);
        $this->assertGeneratedCodeQuality($generated);
    }

    /**
     * Check formatting and Rector rules on freshly generated PHP.
     *
     * @param array<string, string> $files Generated paths mapped to their intended application paths.
     * @return void
     */
    protected function assertGeneratedCodeQuality(array $files): void
    {
        foreach ($files as $file => $relativePath) {
            $this->assertCodingStandard($file, $relativePath);
        }

        // Run once per generated stack rather than starting Rector for every file.
        $root = Plugin::path('Bake');
        $command = [
            PHP_BINARY,
            $root . 'vendor/bin/rector',
            'process',
            '--config=' . $root . 'tests/rector-generated.php',
            '--autoload-file=' . $root . 'vendor/autoload.php',
            '--dry-run',
            '--debug',
            '--clear-cache',
            '--no-progress-bar',
            '--no-ansi',
            ...array_keys($files),
        ];
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $status = proc_close($process);
        $report = $output . $errors;

        $this->assertSame(0, $status, "Rector reported issues in generated PHP:\n" . $report);
        foreach (array_keys($files) as $file) {
            $this->assertStringContainsString('[file] ' . $file, $report, 'Rector did not inspect ' . $file);
        }
    }

    /**
     * Check code using its intended application path so test-fixture exclusions do not hide violations.
     *
     * @param string $file Generated PHP file.
     * @param string $relativePath Intended path within an application or plugin.
     * @return void
     */
    protected function assertCodingStandard(string $file, string $relativePath): void
    {
        $this->assertFileExists($file);
        $stdinPath = '/bake-generated/' . str_replace('\\', '/', $relativePath);
        $command = [
            PHP_BINARY,
            Plugin::path('Bake') . 'vendor/bin/phpcs',
            '--standard=CakePHP',
            '--report=json',
            '--stdin-path=' . $stdinPath,
            '-q',
            '-',
        ];
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        fwrite($pipes[0], file_get_contents($file));
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $status = proc_close($process);
        $report = json_decode($output, true, flags: JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey($stdinPath, $report['files'], 'PHPCS did not inspect ' . $file);
        $messages = json_encode($report['files'][$stdinPath]['messages'], JSON_PRETTY_PRINT);
        $this->assertSame(0, $status, $file . ":\n" . $messages . $errors);
        $this->assertSame(['errors' => 0, 'warnings' => 0, 'fixable' => 0], $report['totals']);
    }
}
