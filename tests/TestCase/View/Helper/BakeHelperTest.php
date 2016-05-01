<?php
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
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\Stub\Response;
use Cake\TestSuite\TestCase;

/**
 * BakeViewTest class
 *
 */
class BakeHelperTest extends TestCase
{

    /**
     * fixtures
     *
     * Don't sort this list alphabetically - otherwise there are table constraints
     * which fail when using postgres
     *
     * @var array
     */
    public $fixtures = [
        'plugin.bake.bake_articles',
        'plugin.bake.bake_comments',
        'plugin.bake.bake_articles_bake_tags',
        'plugin.bake.bake_tags',
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
    public function setUp()
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
    public function tearDown()
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
        $table = TableRegistry::get('Articles', [
            'className' => '\Bake\Test\App\Model\Table\ArticlesTable'
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
        $table = TableRegistry::get('Articles', [
                    'className' => '\Bake\Test\App\Model\Table\ArticlesTable'
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
        $table = TableRegistry::get('Articles', [
                    'className' => '\Bake\Test\App\Model\Table\ArticlesTable'
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
            $spaces . $spaces . "'three'\n" .
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
            $spaces . $spaces . $spaces . "'three'\n" .
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
            $spaces . $spaces . $spaces . "'three'\n" .
            $spaces . $spaces;
        $this->assertSame($expected, $result);
    }

    /**
     * test stringifyList with trailingComma
     *
     * @return void
     */
    public function testStringifyListWithCommaAtEnd()
    {
        $list = ['one' => 'foo', 'two' => 'bar', 'three'];
        $result = $this->BakeHelper->stringifyList($list, [
            'indent' => 3,
            'tab' => "\t",
            'trailingComma' => true,
        ]);
        $spaces = "\t";
        $expected = "\n" .
            $spaces . $spaces . $spaces . "'one' => 'foo',\n" .
            $spaces . $spaces . $spaces . "'two' => 'bar',\n" .
            $spaces . $spaces . $spaces . "'three',\n" .
            $spaces . $spaces;
        $this->assertSame($expected, $result);
    }
}
