<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         2.8.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\CodeGen;

use Bake\CodeGen\CodeParser;
use Bake\CodeGen\FileBuilder;
use Bake\CodeGen\ParseException;
use Bake\Test\TestCase\TestCase;
use Cake\Log\Log;
use InvalidArgumentException;

class FileBuilderTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
        Log::drop('parser');
    }

    public function testMismatchedNamespace(): void
    {
        $parser = new CodeParser();
        $file = $parser->parseFile(<<<'PARSE'
<?php

namespace MyApp\Model;

class TestTable{}
PARSE
        );

        $this->expectException(ParseException::class);
        $builder = new FileBuilder('MyOtherApp\Model', $file);
    }

    public function testClassImports(): void
    {
        $parser = new CodeParser();
        $file = $parser->parseFile(<<<'PARSE'
<?php

namespace MyApp\Model;

use Cake\ORM\Table;
use MyApp\Expression\MyExpression;
use RuntimeException as MyException;

class TestTable{}
PARSE
        );

        $builder = new FileBuilder('MyApp\Model', $file);

        // Pass required imports out of order
        $imports = $builder->getClassImports(['Table' => 'Cake\ORM\Table', 'Cake\ORM\Query']);
        $this->assertSame(
            [
                'Query' => 'Cake\ORM\Query',
                'Table' => 'Cake\ORM\Table',
                'MyExpression' => 'MyApp\Expression\MyExpression',
                'MyException' => 'RuntimeException',
            ],
            $imports
        );

        // Build without existing file
        $builder = new FileBuilder('MyApp\Model');
        $imports = $builder->getClassImports(['Cake\ORM\Table', 'Cake\ORM\Query']);
        $this->assertSame(
            [
                'Query' => 'Cake\ORM\Query',
                'Table' => 'Cake\ORM\Table',
            ],
            $imports
        );
    }

    public function testImportConflictDuplicateGenerated(): void
    {
        $builder = new FileBuilder('MyApp\Model', null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot specify duplicate import for `Cake\ORM\Query`');
        $builder->getClassImports(['Cake\ORM\Query', 'MyQuery' => 'Cake\ORM\Query']);
    }

    public function testImportConflictUserClass(): void
    {
        Log::setConfig('parser', [
            'className' => 'Array',
            'levels' => ['warning'],
        ]);

        $parser = new CodeParser();
        $file = $parser->parseFile(<<<'PARSE'
<?php

namespace MyApp\Model;

use Cake\ORM\Query as MyQuery;

class TestTable{}
PARSE
        );

        $builder = new FileBuilder('MyApp\Model', $file);

        $builder->getClassImports(['Cake\ORM\Query']);
        $this->assertSame(
            ['warning: Import conflict: `Cake\ORM\Query` in generated code is already imported with a different alias, discarding'],
            Log::engine('parser')->read()
        );
    }

    public function testImportConflictUserAlias(): void
    {
        Log::setConfig('parser', [
            'className' => 'Array',
            'levels' => ['warning'],
        ]);

        $parser = new CodeParser();
        $file = $parser->parseFile(<<<'PARSE'
<?php

namespace MyApp\Model;

use MyApp\Query as Query;

class TestTable{}
PARSE
        );

        $builder = new FileBuilder('MyApp\Model', $file);

        $builder->getClassImports(['Cake\ORM\Query']);
        $this->assertSame(
            ['warning: Import conflict: alias `Query` is already being used by generated code, discarding'],
            Log::engine('parser')->read()
        );
    }
}
