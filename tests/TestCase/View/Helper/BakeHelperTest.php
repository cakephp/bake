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
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\View\Helper;

use Bake\View\BakeView;
use Bake\View\Helper\BakeHelper;
use Cake\Http\Response;
use Cake\Http\ServerRequest as Request;
use Cake\TestSuite\TestCase;

/**
 * BakeViewTest class
 */
class BakeHelperTest extends TestCase
{
    /**
     * fixtures
     *
     * Don't sort this list alphabetically - otherwise there are table constraints
     * which fail when using postgres
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'plugin.Bake.BakeArticles',
        'plugin.Bake.BakeComments',
        'plugin.Bake.BakeArticlesBakeTags',
        'plugin.Bake.BakeTags',
    ];

    /**
     * @var BakeView
     */
    protected $View;

    /**
     * @var BakeHelper
     */
    protected $BakeHelper;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $request = new Request();
        $response = new Response();
        $this->View = new BakeView($request, $response);
        $this->BakeHelper = new BakeHelper($this->View);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->BakeHelper);
    }

    /**
     * test extracting aliases and filtering the hasMany aliases correctly based on belongsToMany
     *
     * @return void
     */
    public function testAliasExtractorFilteredHasMany()
    {
        $table = $this->getTableLocator()->get('Articles', [
            'className' => '\Bake\Test\App\Model\Table\ArticlesTable',
        ]);
        $this->BakeHelper = $this->getMockBuilder('Bake\View\Helper\BakeHelper')
                ->disableOriginalConstructor()
                ->setMethods(['_filterHasManyAssociationsAliases'])
                ->getMock();
        $this->BakeHelper->expects($this->once())
                ->method('_filterHasManyAssociationsAliases')
                ->with($table, ['ArticlesTags']);
        $result = $this->BakeHelper->aliasExtractor($table, 'HasMany');
        $this->assertEmpty($result);
    }

    /**
     * test extracting belongsTo
     *
     * @return void
     */
    public function testAliasExtractorBelongsTo()
    {
        $table = $this->getTableLocator()->get('Articles', [
                    'className' => '\Bake\Test\App\Model\Table\ArticlesTable',
        ]);
        $result = $this->BakeHelper->aliasExtractor($table, 'BelongsTo');
        $expected = ['authors'];
        $this->assertSame($expected, $result);
    }

    /**
     * test extracting belongsToMany
     *
     * @return void
     */
    public function testAliasExtractorBelongsToMany()
    {
        $table = $this->getTableLocator()->get('Articles', [
                    'className' => '\Bake\Test\App\Model\Table\ArticlesTable',
        ]);
        $result = $this->BakeHelper->aliasExtractor($table, 'BelongsToMany');
        $expected = ['tags'];
        $this->assertSame($expected, $result);
    }

    /**
     * test stringifyList empty
     *
     * @return void
     */
    public function testStringifyListEmpty()
    {
        $result = $this->BakeHelper->stringifyList([]);
        $expected = '';
        $this->assertSame($expected, $result);
    }

    /**
     * test stringifyList defaults
     *
     * @return void
     */
    public function testStringifyListDefaults()
    {
        $list = ['one' => 'foo', 'two' => 'bar', 'three'];
        $result = $this->BakeHelper->stringifyList($list);
        $spaces = '    ';
        $expected = "\n" .
            $spaces . $spaces . "'one' => 'foo',\n" .
            $spaces . $spaces . "'two' => 'bar',\n" .
            $spaces . $spaces . "'three',\n" .
            $spaces;
        $this->assertSame($expected, $result);
    }

    /**
     * test stringifyList indent is false
     *
     * @return void
     */
    public function testStringifyListIndentIsFalse()
    {
        $list = ['one' => 'foo', 'two' => 'bar', 'three'];
        $result = $this->BakeHelper->stringifyList($list, ['indent' => false]);
        $expected = "'one' => 'foo', 'two' => 'bar', 'three'";
        $this->assertSame($expected, $result);
    }

    /**
     * test stringifyList deeper indent
     *
     * @return void
     */
    public function testStringifyListDeeperIndent()
    {
        $list = ['one' => 'foo', 'two' => 'bar', 'three'];
        $result = $this->BakeHelper->stringifyList($list, ['indent' => 3]);
        $spaces = '    ';
        $expected = "\n" .
            $spaces . $spaces . $spaces . "'one' => 'foo',\n" .
            $spaces . $spaces . $spaces . "'two' => 'bar',\n" .
            $spaces . $spaces . $spaces . "'three',\n" .
            $spaces . $spaces;
        $this->assertSame($expected, $result);
    }

    /**
     * test stringifyList other tab
     *
     * @return void
     */
    public function testStringifyListOtherTab()
    {
        $list = ['one' => 'foo', 'two' => 'bar', 'three'];
        $result = $this->BakeHelper->stringifyList($list, ['indent' => 3, 'tab' => "\t"]);
        $spaces = "\t";
        $expected = "\n" .
            $spaces . $spaces . $spaces . "'one' => 'foo',\n" .
            $spaces . $spaces . $spaces . "'two' => 'bar',\n" .
            $spaces . $spaces . $spaces . "'three',\n" .
            $spaces . $spaces;
        $this->assertSame($expected, $result);
    }

    /**
     * test stringifyList with trailingComma
     *
     * @return void
     */
    public function testStringifyListWithNoCommaAtEnd()
    {
        $list = ['one' => 'foo', 'two' => 'bar', 'three'];
        $result = $this->BakeHelper->stringifyList($list, [
            'indent' => 3,
            'tab' => "\t",
            'trailingComma' => false,
        ]);
        $spaces = "\t";
        $expected = "\n" .
            $spaces . $spaces . $spaces . "'one' => 'foo',\n" .
            $spaces . $spaces . $spaces . "'two' => 'bar',\n" .
            $spaces . $spaces . $spaces . "'three'\n" .
            $spaces . $spaces;
        $this->assertSame($expected, $result);
    }

    /**
     * test escapeArgument with integers and strings
     *
     * @return void
     */
    public function testEscapeArguments()
    {
        $arguments = [
            100,
            "foo 'bar'",
            'foo "bar"',
        ];
        $result = $this->BakeHelper->escapeArguments($arguments);
        $expected = [
            100,
            "'foo \'bar\''",
            "'foo \"bar\"'",
        ];
        $this->assertSame($expected, $result);
    }

    public function testGetUseStatements(): void
    {
        $statements = $this->BakeHelper->getClassUseStatements(['Cake\ORM\Query', 'Table' => 'Cake\ORM\Table', 'MyException' => 'RuntimeException']);
        $this->assertSame(
            [
                'use Cake\ORM\Query;',
                'use Cake\ORM\Table;',
                'use RuntimeException as MyException;',
            ],
            $statements
        );

        $statements = $this->BakeHelper->getFunctionUseStatements(['MyApp\my_function', 'custom_implode' => 'implode']);
        $this->assertSame(
            [
                'use function MyApp\my_function;',
                'use function implode as custom_implode;',
            ],
            $statements
        );

        $statements = $this->BakeHelper->getConstUseStatements(['MyApp\MY_CONSTANT', 'CUSTOM_DATE' => 'DATE_ATOM']);
        $this->assertSame(
            [
                'use const MyApp\MY_CONSTANT;',
                'use const DATE_ATOM as CUSTOM_DATE;',
            ],
            $statements
        );
    }

    public function testConcatCode(): void
    {
        $statements = [
            'use Cake\ORM\Query;',
            'use RuntimeException as MyException;',
            '',
        ];
        $code = $this->BakeHelper->concatCode("\n", $statements);
        $this->assertSame(
            <<<'CODE'
            use Cake\ORM\Query;
            use RuntimeException as MyException;
            CODE,
            $code
        );

        $code = $this->BakeHelper->concatCode("\n", $statements, "\n", "\n");
        $this->assertSame(
            <<<'CODE'

            use Cake\ORM\Query;
            use RuntimeException as MyException;

            CODE,
            $code
        );
    }
}
