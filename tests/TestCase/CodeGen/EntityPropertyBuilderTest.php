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

namespace Bake\Test\TestCase\CodeGen;

use Bake\CodeGen\EntityPropertyBuilder;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * EntityPropertyBuilder Test
 */
#[CoversClass(EntityPropertyBuilder::class)]
class EntityPropertyBuilderTest extends TestCase
{
    /**
     * @var EntityPropertyBuilder
     */
    protected $builder;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new EntityPropertyBuilder();
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
            $this->builder->buildEntityPropertyDeclarations($this->propertySchema(), $imports, $namespace),
        );

        // Without namespace or imports fully qualified class names are used.
        $this->assertSame(
            'public protected(set) ?\App\Model\Entity\User $author;',
            $this->builder->buildEntityPropertyDeclarations(['author' => $this->propertySchema()['author']])['author'],
        );

        // Classes part of the namespace shadowed by an import keep their FQCN.
        $this->assertSame(
            'public protected(set) ?\App\Model\Entity\User $author;',
            $this->builder->buildEntityPropertyDeclarations(
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
            $this->builder->buildEntityPropertyImports($schema, 'App\Model\Entity'),
        );

        $this->assertSame(
            [
                'Cake\I18n\Date',
                'App\Model\Entity\User',
                'App\Other\Entity\Editor',
            ],
            $this->builder->buildEntityPropertyImports($schema),
        );
    }

    /**
     * Tests the columnTypeToHintType method
     *
     * @return void
     */
    public function testColumnTypeToHintType(): void
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
                $this->assertSame($return, $this->builder->columnTypeToHintType($colType));
            }
        }
    }

    /**
     * Tests the associatedEntityTypeToHintType method
     *
     * @return void
     */
    public function testAssociatedEntityTypeToHintType(): void
    {
        $sourceTable = new Table(['alias' => 'Source']);

        $this->assertSame(
            'array<Foo>',
            $this->builder->associatedEntityTypeToHintType('Foo', new HasMany('Foo', $sourceTable)),
        );
        $this->assertSame(
            'Pong',
            $this->builder->associatedEntityTypeToHintType('Pong', new BelongsTo('Pong', $sourceTable)),
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
}
