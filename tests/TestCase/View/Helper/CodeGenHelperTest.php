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
namespace Bake\Test\TestCase\View\Helper;

use Bake\View\BakeView;
use Bake\View\Helper\CodeGenHelper;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

/**
 * BakeViewTest class
 */
class CodeGenHelperTest extends TestCase
{
    /**
     * @var \Bake\View\Helper\CodeGenHelper
     */
    protected $CodeGenHelper;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->CodeGenHelper = new CodeGenHelper(new BakeView(new ServerRequest(), new Response()));
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->CodeGenHelper);
    }

    public function testGetImports(): void
    {
        $code = $this->CodeGenHelper->getImports(
            ['Cake\ORM\Query', 'Table' => 'Cake\ORM\Table', 'MyException' => 'RuntimeException'],
            ['MyApp\my_function', 'custom_implode' => 'implode'],
            ['MyApp\MY_CONSTANT', 'CUSTOM_DATE' => 'DATE_ATOM'],
            "prefix;\n",
            "\nsuffix;"
        );
        $this->assertSame(
            <<<'PARSE'
prefix;
use Cake\ORM\Query;
use Cake\ORM\Table;
use RuntimeException as MyException;
use function MyApp\my_function;
use function implode as custom_implode;
use const MyApp\MY_CONSTANT;
use const DATE_ATOM as CUSTOM_DATE;
suffix;
PARSE
            ,
            $code
        );
    }

    public function testConcat(): void
    {
        $statements = [
            'use Cake\ORM\Query;',
            'use RuntimeException as MyException;',
            '',
        ];
        $code = $this->CodeGenHelper->concat("\n", $statements);
        $this->assertSame(
            <<<'PARSE'
use Cake\ORM\Query;
use RuntimeException as MyException;
PARSE
            ,
            $code
        );

        $code = $this->CodeGenHelper->concat("\n", $statements, "\n", "\n");
        $this->assertSame(
            <<<'PARSE'

use Cake\ORM\Query;
use RuntimeException as MyException;

PARSE
            ,
            $code
        );
    }
}
