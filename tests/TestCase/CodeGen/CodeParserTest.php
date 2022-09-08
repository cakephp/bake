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
use Bake\CodeGen\ParseException;
use Bake\Test\TestCase\TestCase;

class CodeParserTest extends TestCase
{
    public function testParseFile(): void
    {
        $parser = new CodeParser();
        $file = $parser->parseFile(file_get_contents(APP . DS . 'Model' . DS . 'Table' . DS . 'ParseTestTable.php'));

        $this->assertSame('Bake\Test\App\Model\Table', $file->namespace);
        $this->assertSame('ParseTestTable', $file->class->name);
        $this->assertSame(
            [
                'Query' => 'Cake\ORM\Query',
                'RulesChecker' => 'Cake\ORM\RulesChecker',
                'Table' => 'Cake\ORM\Table',
                'Validator' => 'Cake\Validation\Validator',
            ],
            $file->imports['class']
        );
        $this->assertSame(
            [
                'SOME_CONST',
            ],
            array_keys($file->class->constants)
        );
        $this->assertSame(
            [
                'withDocProperty',
                'withoutDocProperty',
            ],
            array_keys($file->class->properties)
        );
        $this->assertSame(
            [
                'initialize',
                'buildRules',
                'validationDefault',
                'defaultConnectionName',
                'findTest',
            ],
            array_keys($file->class->methods)
        );

        $code = <<<'PARSE'
    /**
     * @var int
     */
    protected const SOME_CONST = 1;
PARSE;
        $this->assertSame($code, $file->class->constants['SOME_CONST']);

        $code = <<<'PARSE'
    /**
     * @var string
     */
    protected $withDocProperty = <<<'TEXT'
    BLOCK OF TEXT
TEXT;
PARSE;
        $this->assertSame($code, $file->class->properties['withDocProperty']);

        $code = <<<'PARSE'
    protected $withoutDocProperty = 1;
PARSE;
        $this->assertSame($code, $file->class->properties['withoutDocProperty']);

        $code = <<<'PARSE'
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('test');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }
PARSE;
        $this->assertSame($code, $file->class->methods['initialize']);
    }

    public function testUseStatements(): void
    {
        $parser = new CodeParser();
        $file = $parser->parseFile(<<<'PARSE'
<?php

namespace Test;

use Test\Another\ClassA;
use Test\Another\ClassC as C;

use function Test\Another\test_func;
use function Test\Another\test_func2 as new_func;

use const Test\Another\TEST_CONSTANT;
use const Test\Another\TEST_CONSTANT2 as NEW_CONSTANT;

class TestTable{}
PARSE
        );

        $this->assertSame(
            [
                'class' => [
                    'ClassA' => 'Test\Another\ClassA',
                    'C' => 'Test\Another\ClassC',
                ],
                'function' => [
                    'test_func' => 'Test\Another\test_func',
                    'new_func' => 'Test\Another\test_func2',

                ],
                'const' => [
                    'TEST_CONSTANT' => 'Test\Another\TEST_CONSTANT',
                    'NEW_CONSTANT' => 'Test\Another\TEST_CONSTANT2',
                ],
            ],
            $file->imports
        );
    }

    public function testParseMissingClass(): void
    {
        $parser = new CodeParser();

        $file = $parser->parseFile(<<<'PARSE'
<?php

namespace Bake\Test;
PARSE
        );
        $this->assertNull($file);
    }

    public function testParseMissingNamespace(): void
    {
        $parser = new CodeParser();

        $this->expectException(ParseException::class);
        $parser->parseFile(<<<'PARSE'
<?php

class TestTable{}
PARSE
        );
    }

    public function testParseMultipleNamespaces(): void
    {
        $parser = new CodeParser();

        $this->expectException(ParseException::class);
        $parser->parseFile(<<<'PARSE'
<?php

namespace Bake\Test;

class TestTable{}

namespace Bake\Test2;

class Test2Table{}
PARSE
        );
    }

    public function testParseMultipleUses(): void
    {
        $parser = new CodeParser();

        $this->expectException(ParseException::class);
        $parser->parseFile(<<<'PARSE'
<?php

namespace Bake\Test;

use Cake\ORM\Query, Cake\ORM\Table;

class TestTable{}
PARSE
        );
    }

    public function testParseGroupUses(): void
    {
        $parser = new CodeParser();

        $this->expectException(ParseException::class);
        $parser->parseFile(<<<'PARSE'
<?php

namespace Bake\Test;

use Cake\ORM\{Query, Table};

class TestTable{}
PARSE
        );
    }
}
