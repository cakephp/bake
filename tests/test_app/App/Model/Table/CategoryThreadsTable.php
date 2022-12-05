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

use Cake\ORM\Table;

/**
 * CategoryThreadsTable
 */
class CategoryThreadsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->setTable('category_threads');
        $this->belongsTo(
            'ParentCategoryThreads',
            [
            'className' => self::class,
            'foreignKey' => 'parent_id',
            ]
        );
        $this->hasMany(
            'ChildCategoryThreads',
            [
            'className' => self::class,
            'foreignKey' => 'parent_id',
            ]
        );
        $this->addBehavior('Tree');
    }
}
