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
namespace Bake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BakeArticleFixture
 *
 */
class BakeArticlesFixture extends TestFixture
{
    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'comment' => 'ID'],
        'bake_user_id' => ['type' => 'integer', 'null' => false],
        'title' => ['type' => 'string', 'comment' => 'Title', 'length' => 50, 'null' => false],
        'body' => ['type' => 'text', 'comment' => 'Contents'],
        'rating' => ['type' => 'float', 'comment' => 'Rating', 'unsigned' => true, 'default' => 0.0, 'null' => false],
        'score' => ['type' => 'decimal', 'comment' => 'Score', 'unsigned' => true, 'default' => 0.0, 'null' => false],
        'published' => ['type' => 'boolean', 'comment' => 'Is Published', 'length' => 1, 'default' => false, 'null' => false],
        'created' => ['type' => 'datetime', 'comment' => 'Creation date'],
        'updated' => ['type' => 'datetime', 'comment' => 'Modification date'],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [];
}
