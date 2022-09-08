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

    public function testUses(): void
    {
        $parser = new CodeParser();
        $file = $parser->parseFile(<<<'PARSE'
<?php

namespace MyApp\Model;

use Cake\ORM\Table;
use MyApp\Expression\MyExpression;
use RuntimeException as MyException;
use function MyApp\my_function;
use function implode as custom_implode;
use const MyApp\MY_CONSTANT;
use const DATE_ATOM as CUSTOM_DATE;

class TestTable{}
PARSE
        );

        $builder = new FileBuilder('MyApp\Model', $file);

        // Pass required imports out of order
        $uses = $builder->getUses(['Table' => 'Cake\ORM\Table', 'Cake\ORM\Query']);
        $this->assertSame(
            [
                'class' => [
                    'use Cake\ORM\Query;',
                    'use Cake\ORM\Table;',
                    'use MyApp\Expression\MyExpression;',
                    'use RuntimeException as MyException;',
                ],
                'function' => [
                    'use function implode as custom_implode;',
                    'use function MyApp\my_function;',
                ],
                'const' => [
                    'use const DATE_ATOM as CUSTOM_DATE;',
                    'use const MyApp\MY_CONSTANT;',
                ],
            ],
            $uses
        );

        // Build without existing file
        $builder = new FileBuilder('MyApp\Model');
        $uses = $builder->getUses(['Cake\ORM\Table', 'Cake\ORM\Query'], ['implode'], ['DATE_ATOM']);
        $this->assertSame(
            [
                'class' => [
                    'use Cake\ORM\Query;',
                    'use Cake\ORM\Table;',
                ],
                'function' => [
                    'use function implode;',
                ],
                'const' => [
                    'use const DATE_ATOM;',
                ],
            ],
            $uses
        );
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

        $builder->getUses(['Cake\ORM\Query']);
        $this->assertSame(
            ['warning: User import `Cake\ORM\Query` conflicts with generated import, discarding'],
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

        $builder->getUses(['Cake\ORM\Query']);
        $this->assertSame(
            ['warning: User import `MyApp\Query` conflicts with generated import, discarding'],
            Log::engine('parser')->read()
        );
    }
}
