<?php
/**
 * CakePHP(tm) Tests <http://book.cakephp.org/3.0/en/development/testing.html>
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/3.0/en/development/testing.html CakePHP(tm) Tests
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Utility\Model;

use Bake\Utility\Model\AssociationFilter;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

/**
 * BakeViewTest class
 *
 */
class AssociationFilterTest extends TestCase
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
        'core.authors',
        'core.tags',
        'plugin.bake.bake_articles',
        'plugin.bake.bake_comments',
        'plugin.bake.bake_articles_bake_tags',
        'plugin.bake.bake_tags',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->associationFilter = new AssociationFilter();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        TableRegistry::clear();
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
        $table = TableRegistry::get('Articles', [
            'className' => '\Bake\Test\App\Model\Table\ArticlesTable'
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
        $table = TableRegistry::get('Articles', [
            'className' => '\Bake\Test\App\Model\Table\ArticlesTable'
        ]);
        $table->hasMany('ExtraArticles', [
            'className' => 'Articles'
        ]);
        $result = $this->associationFilter->filterHasManyAssociationsAliases($table, [
            'ExtraArticles',
            'ArticlesTags',
            'AnotherHasMany'
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
        $table = TableRegistry::get('Articles', [
            'className' => '\Bake\Test\App\Model\Table\ArticlesTable'
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
     * testFilterAssociations
     *
     * @return void
     */
    public function testFilterAssociationsMissingTable()
    {
        $table = TableRegistry::get('Articles', [
            'className' => '\Bake\Test\App\Model\Table\ArticlesTable'
        ]);
        $table->hasMany('Nopes');

        $result = $this->associationFilter->filterAssociations($table);
        $this->assertArrayNotHasKey('HasMany', $result);
    }
}
