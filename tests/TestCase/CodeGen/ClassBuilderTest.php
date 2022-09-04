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
namespace Bake\Test\TestCase\CodeGen;

use Bake\CodeGen\CodeParser;
use Bake\CodeGen\FileBuilder;
use Bake\Test\TestCase\TestCase;

class ClassBuilderTest extends TestCase
{
    public function testExistingMethods(): void
    {
        $parser = new CodeParser();
        $file = $parser->parseFile(<<<'PARSE'
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
        PARSE);

        $builder = new FileBuilder('MyApp\Model', $file);
        $methods = $builder->classBuilder()->getExistingMethods(['buildRules']);
        $this->assertSame(
            [
                'findSomething',
                'findSomethingElse',
            ],
            array_keys($methods)
        );
    }
}
