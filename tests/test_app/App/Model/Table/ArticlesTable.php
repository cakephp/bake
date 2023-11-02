<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.1.0
 * @license   https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\App\Model\Table;

use Bake\Test\App\Model\Enum\ArticleStatus;
use Cake\Database\Type\EnumType;
use Cake\ORM\Table;

/**
 * Article table class
 */
class ArticlesTable extends Table
{
    public function initialize(array $config): void
    {
        $this->belongsTo('authors');
        $this->belongsToMany('tags');
        $this->hasMany('ArticlesTags');

        $this->getSchema()->setColumnType('published', EnumType::from(ArticleStatus::class));
    }

    /**
     * Find published
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findPublished($query)
    {
        return $query->where(['published' => 'Y']);
    }

    /**
     * Example public method
     *
     * @return void
     */
    public function doSomething()
    {
    }

    /**
     * Example Secondary public method
     *
     * @return void
     */
    public function doSomethingElse()
    {
    }

    /**
     * Example protected method
     *
     * @return void
     */
    protected function _innerMethod()
    {
    }
}
