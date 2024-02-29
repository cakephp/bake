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
namespace Bake\Test\TestCase\Utility\Model;

use Bake\Utility\Model\AssociationFilter;
use Cake\TestSuite\TestCase;

/**
 * BakeViewTest class
 */
class AssociationFilterTest extends TestCase
{
    /**
     * fixtures
     *
     * Don't sort this list alphabetically - otherwise there are table constraints
     * which fail when using postgres
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'plugin.Bake.Authors',
        'plugin.Bake.Tags',
        'plugin.Bake.Articles',
        'plugin.Bake.BakeArticles',
        'plugin.Bake.BakeComments',
        'plugin.Bake.BakeArticlesBakeTags',
        'plugin.Bake.BakeTags',
        'plugin.Bake.CategoryThreads',
    ];

    /**
     * @var AssociationFilter
     */
    protected $associationFilter;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->associationFilter = new AssociationFilter();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->getTableLocator()->clear();
        unset($this->associationFilter);
        parent::tearDown();
    }

    /**
     * test extracting aliases and filtering the hasMany aliases correctly based on belongsToMany
     *
     * @return void
     */
    public function testFilterHasManyAssociationsAliases()
    {
        $table = $this->getTableLocator()->get('Articles', [
            'className' => '\Bake\Test\App\Model\Table\ArticlesTable',
        ]);
        $result = $this->associationFilter->filterHasManyAssociationsAliases($table, ['ArticlesTags']);
        $expected = [];
        $this->assertSame(
            $expected,
            $result,
            'hasMany should filter results based on belongsToMany existing aliases'
        );
    }

    /**
     * test extracting extra HasMany
     *
     * @return void
     */
    public function testFilterHasManyAssociationsAliasesExtra()
    {
        $table = $this->getTableLocator()->get('Articles', [
            'className' => '\Bake\Test\App\Model\Table\ArticlesTable',
        ]);
        $table->hasMany('ExtraArticles', [
            'className' => 'Articles',
        ]);
        $result = $this->associationFilter->filterHasManyAssociationsAliases($table, [
            'ExtraArticles',
            'ArticlesTags',
            'AnotherHasMany',
        ]);
        $expected = ['ExtraArticles', 'AnotherHasMany'];
        $this->assertSame(
            $expected,
            $result,
            'hasMany should filter results based on belongsToMany existing aliases'
        );
        $table->associations()->remove('ExtraArticles');
    }

    /**
     * testFilterAssociations
     *
     * @return void
     */
    public function testFilterAssociations()
    {
        $table = $this->getTableLocator()->get('Articles', [
            'className' => '\Bake\Test\App\Model\Table\ArticlesTable',
        ]);
        $resultAssociations = $this->associationFilter->filterAssociations($table);
        $result = [];
        foreach ($resultAssociations as $assoc) {
            $aliases = array_keys($assoc);
            foreach ($aliases as $alias) {
                $result[] = $alias;
            }
        }
        $expected = ['authors', 'tags'];
        $this->assertEquals($expected, $result);
    }

    /**
     * test filtering self associations
     *
     * @return void
     */
    public function testFilterAssociationsSelf()
    {
        $table = $this->getTableLocator()->get('CategoryThreads', [
            'className' => '\Bake\Test\App\Model\Table\CategoryThreadsTable',
        ]);
        $result = $this->associationFilter->filterAssociations($table);
        $this->assertArrayHasKey('HasMany', $result);
        $this->assertArrayHasKey('BelongsTo', $result);
        $this->assertFalse($result['BelongsTo']['ParentCategoryThreads']['navLink']);
        $this->assertFalse($result['HasMany']['ChildCategoryThreads']['navLink']);
    }

    /**
     * testFilterAssociations
     *
     * @return void
     */
    public function testFilterAssociationsMissingTable()
    {
        $table = $this->getTableLocator()->get('Articles', [
            'className' => '\Bake\Test\App\Model\Table\ArticlesTable',
        ]);
        $table->hasMany('Nopes');

        $result = $this->associationFilter->filterAssociations($table);
        $this->assertArrayNotHasKey('HasMany', $result);
    }
}
