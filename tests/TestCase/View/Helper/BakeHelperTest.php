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
 * @since         0.1.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
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
                ->onlyMethods(['_filterHasManyAssociationsAliases'])
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

    public function testConcat(): void
    {
        $statements = [
            'use Cake\ORM\Query\SelectQuery;',
            'use RuntimeException as MyException;',
            '',
        ];
        $code = $this->BakeHelper->concat("\n", $statements);
        $this->assertSame(
            <<<'PARSE'
use Cake\ORM\Query\SelectQuery;
use RuntimeException as MyException;
PARSE
            ,
            $code
        );

        $code = $this->BakeHelper->concat("\n", $statements, "\n", "\n");
        $this->assertSame(
            <<<'PARSE'

use Cake\ORM\Query\SelectQuery;
use RuntimeException as MyException;

PARSE
            ,
            $code
        );
    }
}
