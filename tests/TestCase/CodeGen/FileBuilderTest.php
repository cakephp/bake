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
 * @since         2.8.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\CodeGen;

use Bake\CodeGen\CodeParser;
use Bake\CodeGen\FileBuilder;
use Bake\CodeGen\ParseException;
use Bake\Test\TestCase\TestCase;
use Cake\Console\ConsoleIo;
use Cake\Console\TestSuite\Constraint\ContentsContain;
use Cake\Console\TestSuite\StubConsoleOutput;

class FileBuilderTest extends TestCase
{
    /**
     * @var \Cake\TestSuite\Stub\ConsoleOutput
     */
    protected $out;

    /**
     * @var \Cake\Console\ConsoleIo
     */
    protected $io;

    public function setUp(): void
    {
        parent::setUp();
        $this->out = new StubConsoleOutput();
        $this->io = new ConsoleIo($this->out, $this->out);
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
        new FileBuilder($this->io, 'MyOtherApp\Model', $file);
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

        $builder = new FileBuilder($this->io, 'MyApp\Model', $file);

        // Pass required imports out of order
        $this->assertSame(
            [
                'SelectQuery' => 'Cake\ORM\Query\SelectQuery',
                'Table' => 'Cake\ORM\Table',
                'MyExpression' => 'MyApp\Expression\MyExpression',
                'MyException' => 'RuntimeException',
            ],
            $builder->getClassImports(['Table' => 'Cake\ORM\Table', 'Cake\ORM\Query\SelectQuery'])
        );

        $this->assertSame(
            [
                'custom_implode' => 'implode',
                'my_function' => 'MyApp\my_function',
            ],
            $builder->getFunctionImports()
        );

        $this->assertSame(
            [
                'CUSTOM_DATE' => 'DATE_ATOM',
                'MY_CONSTANT' => 'MyApp\MY_CONSTANT',
            ],
            $builder->getConstImports()
        );

        // Build without existing file
        $builder = new FileBuilder($this->io, 'MyApp\Model');
        $this->assertSame(
            [
                'SelectQuery' => 'Cake\ORM\Query\SelectQuery',
                'Table' => 'Cake\ORM\Table',
            ],
            $builder->getClassImports(['Cake\ORM\Table', 'Cake\ORM\Query\SelectQuery'])
        );

        $this->assertSame(
            [
                'implode' => 'implode',
            ],
            $builder->getFunctionImports(['implode'])
        );

        $this->assertSame(
            [
                'DATE_ATOM' => 'DATE_ATOM',
            ],
            $builder->getConstImports(['DATE_ATOM'])
        );
    }

    public function testImportConflictUserClass(): void
    {
        $parser = new CodeParser();
        $file = $parser->parseFile(<<<'PARSE'
<?php

namespace MyApp\Model;

use Cake\ORM\Query\SelectQuery as MyQuery;

class TestTable{}
PARSE
        );

        $builder = new FileBuilder($this->io, 'MyApp\Model', $file);

        $builder->getClassImports(['Cake\ORM\Query\SelectQuery']);
        $this->assertThat('Import `Cake\ORM\Query\SelectQuery` conflicts with existing import, discarding', new ContentsContain($this->out->messages(), 'output'));
    }

    public function testImportConflictUserAlias(): void
    {
        $parser = new CodeParser();
        $file = $parser->parseFile(<<<'PARSE'
<?php

namespace MyApp\Model;

use MyApp\Query as SelectQuery;

class TestTable{}
PARSE
        );

        $builder = new FileBuilder($this->io, 'MyApp\Model', $file);

        $builder->getClassImports(['Cake\ORM\Query\SelectQuery']);
        $this->assertThat('Import `MyApp\Query` conflicts with existing import, discarding', new ContentsContain($this->out->messages(), 'output'));
    }
}
