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
 * @since         3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Parse;

use Bake\Parse\CodeParser;
use Bake\Parse\ParseException;
use Bake\Test\TestCase\TestCase;

class CodeParserTest extends TestCase
{
    public function testParseClass(): void
    {
        $parser = new CodeParser();
        $class = $parser->parseClass(file_get_contents(APP . DS . 'Model' . DS . 'Table' . DS . 'ParseTestTable.php'));

        $this->assertSame('Bake\Test\App\Model\Table', $class->namespace);
        $this->assertSame('ParseTestTable', $class->name);
        $this->assertSame(
            [
                'Cake\ORM\Query' => 'Query',
                'Cake\ORM\RulesChecker' => 'RulesChecker',
                'Cake\ORM\Table' => 'Table',
                'Cake\Validation\Validator' => 'Validator',
            ],
            $class->uses['classes']
        );
        $this->assertSame(
            [
                'initialize',
                'buildRules',
                'validationDefault',
                'defaultConnectionName',
                'findTest',
            ],
            array_keys($class->methods)
        );

        $code = <<<'CODEMARKER'
            public function initialize(array $config): void
            {
                parent::initialize($config);

                $this->setTable('test');
                $this->setDisplayField('id');
                $this->setPrimaryKey('id');
            }
        CODEMARKER;
        $this->assertSame($code, $class->methods['initialize']->code);

        $doc = <<<'DOCMARKER'
            /**
             * Initialize method
             *
             * @param array $config The configuration for the Table.
             * @return void
             */
        DOCMARKER;
        $this->assertSame($doc, $class->methods['initialize']->docblock);
    }

    public function testParseMissingNamespace(): void
    {
        $parser = new CodeParser();

        $this->expectException(ParseException::class);
        $class = $parser->parseClass(<<<'PARSE'
        <?php

        class TestTable{}
        PARSE);
    }

    public function testParseMissingClass(): void
    {
        $parser = new CodeParser();

        $this->expectException(ParseException::class);
        $class = $parser->parseClass(<<<'PARSE'
        <?php

        namespace Bake\Test;
        PARSE);
    }

    public function testParseMultipleNamespaces(): void
    {
        $parser = new CodeParser();

        $this->expectException(ParseException::class);
        $class = $parser->parseClass(<<<'PARSE'
        <?php

        namespace Bake\Test;

        class TestTable{}

        namespace Bake\Test2;

        class Test2Table{}
        PARSE);
    }
}
