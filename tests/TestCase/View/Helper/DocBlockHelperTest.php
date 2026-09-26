<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.9.5
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Bake\Test\TestCase\View\Helper;

use Bake\View\BakeView;
use Bake\View\Helper\DocBlockHelper;
use Cake\Http\Response;
use Cake\Http\ServerRequest as Request;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Association\HasOne;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;

/**
 * DocBlockHelper Test
 */
#[CoversClass(DocBlockHelper::class)]
class DocBlockHelperTest extends TestCase
{
    /**
     * @var BakeView
     */
    protected $View;

    /**
     * @var DocBlockHelper
     */
    protected $DocBlockHelper;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $request = new Request();
        $response = new Response();
        $this->View = new BakeView($request, $response);
        $this->DocBlockHelper = new DocBlockHelper($this->View);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->DocBlockHelper);
    }

    /**
     * Tests the classDescription method
     *
     * @return void
     */
    public function testClassDescription(): void
    {
        $className = 'Comments';
        $classType = 'Model';
        $lines = [
            'Line 1',
            '@foo $bar baz',
            '@see there',
        ];
        $classDescription = $this->DocBlockHelper->classDescription($className, $classType, $lines);
        $expected = "/**\n * Comments Model\n *\n * Line 1\n * @foo \$bar baz\n * @see there\n */";
        $this::assertSame($expected, $classDescription);
    }

    /**
     * Tests the classDescription method with annotation spacing enabled
     *
     * @return void
     */
    public function testClassDescriptionAnnotationSpacing(): void
    {
        $className = 'Comments';
        $classType = 'Model';
        $lines = [
            'Line 1',
            '@foo $bar baz',
            '@see there',
        ];
        $reflection = new ReflectionProperty($this->DocBlockHelper, 'annotationSpacing');
        $reflection->setValue($this->DocBlockHelper, true);

        $classDescription = $this->DocBlockHelper->classDescription($className, $classType, $lines);
        $expected = "/**\n * Comments Model\n *\n * Line 1\n * @foo \$bar baz\n *\n * @see there\n */";
        $this::assertSame($expected, $classDescription);
    }

    /**
     * Tests the associatedEntityTypeToHintType method
     *
     * @return void
     */
    public function testAssociatedEntityTypeToHintType(): void
    {
        $sourceTable = new Table(['alias' => 'Source']);

        // Test with MANY_TO_MANY
        $type = 'Foo';
        $association = new BelongsToMany('Foo', $sourceTable);
        $assocEntityType = $this->DocBlockHelper->associatedEntityTypeToHintType($type, $association);
        $expected = 'array<Foo>';
        $this->assertSame($expected, $assocEntityType);

        // Test with ONE_TO_MANY
        $type = 'Bar';
        $association = new HasMany('Bar', $sourceTable);
        $assocEntityType = $this->DocBlockHelper->associatedEntityTypeToHintType($type, $association);
        $expected = 'array<Bar>';
        $this->assertSame($expected, $assocEntityType);

        // Test with ONE_TO_ONE
        $type = 'Ping';
        $association = new HasOne('Ping', $sourceTable);
        $assocEntityType = $this->DocBlockHelper->associatedEntityTypeToHintType($type, $association);
        $expected = 'Ping';
        $this->assertSame($expected, $assocEntityType);

        // Test with MANY_TO_ONE
        $type = 'Pong';
        $association = new BelongsTo('Pong', $sourceTable);
        $assocEntityType = $this->DocBlockHelper->associatedEntityTypeToHintType($type, $association);
        $expected = 'Pong';
        $this->assertSame($expected, $assocEntityType);
    }

    /**
     * Tests the buildEntityPropertyHintTypeMap method
     *
     * @return void
     */
    public function testBuildEntityPropertyHintTypeMap(): void
    {
        $map = [
            'string' => [
                'char',
                'string',
                'text',
                'uuid',
                'decimal',
            ],
            'int' => [
                'integer',
                'biginteger',
                'smallinteger',
                'tinyinteger',
            ],
            'float' => [
                'float',
            ],
            'bool' => [
                'boolean',
            ],
            'array' => [
                'array',
                'json',
            ],
            'string|resource' => [
                'binary',
            ],
            '\Cake\I18n\Date' => [
                'date',
            ],
            '\Cake\I18n\DateTime' => [
                'datetime',
                'datetimefractional',
                'timestamp',
                'timestampfractional',
                'timestamptimezone',
            ],
            '\Cake\I18n\Time' => [
                'time',
            ],
        ];

        foreach ($map as $return => $colTypes) {
            foreach ($colTypes as $colType) {
                $schema = [
                    'col_to_check' => [
                        'type' => $colType,
                        'null' => false,
                        'kind' => 'column',
                    ],
                ];
                $assocEntityType = $this->DocBlockHelper->buildEntityPropertyHintTypeMap($schema);
                $expected = [
                    'col_to_check' => $return,
                ];
                $this->assertEquals($expected, $assocEntityType);
            }
        }
    }

    /**
     * Tests the buildEntityAssociationHintTypeMap method
     *
     * @return void
     */
    public function testBuildEntityAssociationHintTypeMap(): void
    {
        $this->markTestIncomplete('Not implemented yet');
    }

    /**
     * Tests the buildEntityPropertyDeclarations method
     *
     * @return void
     */
    public function testBuildEntityPropertyDeclarations(): void
    {
        $namespace = 'App\Model\Entity';
        $imports = [
            'Date' => 'Cake\I18n\Date',
        ];

        $expected = [
            'id' => 'public protected(set) int $id;',
            'title' => 'public protected(set) ?string $title;',
            'data' => 'public protected(set) ?array $data;',
            // `string|resource` cannot be expressed as a PHP type.
            'file' => 'public protected(set) mixed $file;',
            'published' => 'public protected(set) Date $published;',
            // Classes part of the namespace are referenced by their short name.
            'author' => 'public protected(set) ?User $author;',
            // An array of entities cannot be expressed as a PHP type.
            'revisions' => 'public protected(set) ?array $revisions;',
            // Classes outside the namespace without an import keep their FQCN.
            'editor' => 'public protected(set) ?\App\Other\Entity\Editor $editor;',
        ];
        $this->assertSame(
            $expected,
            $this->DocBlockHelper->buildEntityPropertyDeclarations($this->propertySchema(), $imports, $namespace),
        );

        // Without namespace or imports fully qualified class names are used.
        $this->assertSame(
            'public protected(set) ?\App\Model\Entity\User $author;',
            $this->DocBlockHelper->buildEntityPropertyDeclarations(['author' => $this->propertySchema()['author']])['author'],
        );

        // Classes part of the namespace shadowed by an import keep their FQCN.
        $this->assertSame(
            'public protected(set) ?\App\Model\Entity\User $author;',
            $this->DocBlockHelper->buildEntityPropertyDeclarations(
                ['author' => $this->propertySchema()['author']],
                ['User' => 'App\Other\User'],
                $namespace,
            )['author'],
        );
    }

    /**
     * Tests the buildEntityPropertyImports method
     *
     * @return void
     */
    public function testBuildEntityPropertyImports(): void
    {
        $schema = $this->propertySchema();

        // Classes part of the namespace don't need an import.
        $this->assertSame(
            [
                'Cake\I18n\Date',
                'App\Other\Entity\Editor',
            ],
            $this->DocBlockHelper->buildEntityPropertyImports($schema, 'App\Model\Entity'),
        );

        $this->assertSame(
            [
                'Cake\I18n\Date',
                'App\Model\Entity\User',
                'App\Other\Entity\Editor',
            ],
            $this->DocBlockHelper->buildEntityPropertyImports($schema),
        );
    }

    /**
     * Property schema used for testing the concrete property generation.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function propertySchema(): array
    {
        $sourceTable = new Table(['alias' => 'Source']);

        return [
            'id' => [
                'kind' => 'column',
                'type' => 'integer',
                'null' => false,
            ],
            'title' => [
                'kind' => 'column',
                'type' => 'string',
                'null' => true,
            ],
            'data' => [
                'kind' => 'column',
                'type' => 'array',
                'null' => true,
            ],
            'file' => [
                'kind' => 'column',
                'type' => 'binary',
                'null' => true,
            ],
            'published' => [
                'kind' => 'column',
                'type' => 'date',
                'null' => false,
            ],
            // Fields used by Cake\ORM\Entity itself have to stay dynamic fields.
            'hidden' => [
                'kind' => 'column',
                'type' => 'array',
                'null' => false,
            ],
            'patchable' => [
                'kind' => 'column',
                'type' => 'array',
                'null' => false,
            ],
            'author' => [
                'kind' => 'association',
                'type' => '\App\Model\Entity\User',
                'association' => new BelongsTo('Author', $sourceTable),
            ],
            'revisions' => [
                'kind' => 'association',
                'type' => '\App\Model\Entity\Revision',
                'association' => new HasMany('Revisions', $sourceTable),
            ],
            'editor' => [
                'kind' => 'association',
                'type' => '\App\Other\Entity\Editor',
                'association' => new BelongsTo('Editor', $sourceTable),
            ],
        ];
    }

    /**
     * Tests the columnTypeToHintType method
     *
     * @return void
     */
    public function testColumnTypeToHintType(): void
    {
        $this->markTestIncomplete('Not implemented yet');
    }

    /**
     * Tests the propertyHints method
     *
     * @return void
     */
    public function testPropertyHints(): void
    {
        $this->markTestIncomplete('Not implemented yet');
    }

    /**
     * Tests the buildTableAnnotations method
     *
     * @return void
     */
    public function testBuildTableAnnotations(): void
    {
        $this->markTestIncomplete('Not implemented yet');
    }
}
