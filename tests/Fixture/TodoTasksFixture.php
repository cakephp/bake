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
 * @since         2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TodoTasks
 *
 * Only use this fixture in model command tests
 * so that we don't generate models that effect other tests.
 */
class TodoTasksFixture extends TestFixture
{
    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'uid' => ['type' => 'integer'],
        'todo_item_id' => ['type' => 'integer', 'null' => false],
        'title' => ['type' => 'string', 'length' => 50, 'null' => false],
        'body' => 'text',
        'completed' => ['type' => 'boolean', 'default' => false, 'null' => false],
        'effort' => ['type' => 'decimal', 'default' => 0.0, 'null' => false, 'unsigned' => true],
        'created' => 'datetime',
        'updated' => 'datetime',
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['uid']]],
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [];
}
