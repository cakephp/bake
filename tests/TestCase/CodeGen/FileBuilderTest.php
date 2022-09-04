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
use Bake\CodeGen\ParseException;
use Bake\Test\TestCase\TestCase;

class FileBuilderTest extends TestCase
{
    public function testMismatchedNamespace(): void
    {
        $parser = new CodeParser();
        $file = $parser->parseFile(<<<'PARSE'
        <?php

        namespace MyApp\Model;

        class TestTable{}
        PARSE);

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
        use RuntimeException as MyException2;

        class TestTable{}
        PARSE);

        $builder = new FileBuilder('MyApp\Model', $file);

        // Pass required imports out of order
        $uses = $builder->getClassUses(['Cake\ORM\Table', 'Cake\ORM\Query']);
        $this->assertSame(
            [
                'use Cake\ORM\Query;',
                'use Cake\ORM\Table;',
                'use MyApp\Expression\MyExpression;',
                'use RuntimeException as MyException;',
            ],
            $uses
        );

        $uses = $builder->getClassUses(['Cake\ORM\Table', 'Cake\ORM\Query'], ['MyApp\Expression\MyExpression']);
        $this->assertSame(
            [
                'use Cake\ORM\Query;',
                'use Cake\ORM\Table;',
                'use RuntimeException as MyException;',
            ],
            $uses
        );

        // Build without existing file
        $builder = new FileBuilder('MyApp\Model');
        $uses = $builder->getClassUses(['Cake\ORM\Table', 'Cake\ORM\Query']);
        $this->assertSame(
            [
                'use Cake\ORM\Query;',
                'use Cake\ORM\Table;',
            ],
            $uses
        );
    }
}
