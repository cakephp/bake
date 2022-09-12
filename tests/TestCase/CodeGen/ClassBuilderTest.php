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
use Bake\Test\TestCase\TestCase;
use Cake\Console\ConsoleIo;
use Cake\TestSuite\Stub\ConsoleOutput;

class ClassBuilderTest extends TestCase
{
    /**
     * @var \Cake\Console\ConsoleIo
     */
    protected $io;

    public function setUp(): void
    {
        parent::setUp();
        $this->io = new ConsoleIo(new ConsoleOutput());
    }

    public function testUserConstants(): void
    {
        $parser = new CodeParser();
        $file = $parser->parseFile(
            <<<'PARSE'
<?php

namespace MyApp\Model;

class TestTable
{
    /**
     * @var string
     */
    const GENERATED_CONST = 'string';

    /**
     * @var string
     */
    const MY_CONST = 3;

    /**
     * @param \Cake\ORM\Query $query Query
     * @return \Cake\ORM\Query
     */
    public function findSomething(Query $query): Query
    {
    }
}
PARSE
        );

        $builder = new FileBuilder($this->io, 'MyApp\Model', $file);
        $constants = $builder->classBuilder()->getUserConstants(['GENERATED_CONST']);
        $this->assertSame(
            [
                'MY_CONST',
            ],
            array_keys($constants)
        );
    }

    public function testUserFunctions(): void
    {
        $parser = new CodeParser();
        $file = $parser->parseFile(
            <<<'PARSE'
<?php

namespace MyApp\Model;

use Cake\ORM\Query;
use Cake\ORM\Table;

class TestTable
{
    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);

        return $rules;
    }

    /**
     * @param \Cake\ORM\Query $query Query
     * @return \Cake\ORM\Query
     */
    public function findSomething(Query $query): Query
    {
    }

    /**
     * @param \Cake\ORM\Query $query Query
     * @return \Cake\ORM\Query
     */
    public function findSomethingElse(Query $query): Query
    {
    }
}
PARSE
        );

        $builder = new FileBuilder($this->io, 'MyApp\Model', $file);
        $methods = $builder->classBuilder()->getUserFunctions(['buildRules']);
        $this->assertSame(
            [
                'findSomething',
                'findSomethingElse',
            ],
            array_keys($methods)
        );
    }
}
