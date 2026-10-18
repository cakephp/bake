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

use Bake\Test\TestCase\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Check routing option validation before Bake generates files.
 */
class AttributeRoutingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setAppNamespace('Bake\\Test\\App');
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
}
