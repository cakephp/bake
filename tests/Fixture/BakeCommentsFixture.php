<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BakeCommentFixture fixture for testing bake
 */
class BakeCommentsFixture extends TestFixture
{
    /**
     * fields property
     *
     * @var array<string, mixed>
     */
    public $fields = [
        'otherid' => ['type' => 'integer'],
        'bake_article_id' => ['type' => 'integer', 'null' => false],
        'bake_user_id' => ['type' => 'integer', 'null' => false],
        'comment' => 'text',
        'published' => ['type' => 'string', 'length' => 1, 'default' => 'N'],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'updated' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['otherid']]],
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [];
}
