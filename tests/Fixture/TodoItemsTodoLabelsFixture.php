<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TodoItemsTodoLabels Fixture
 *
 * Only use this fixture in model command tests
 * so that we don't generate models that effect other tests.
 */
class TodoItemsTodoLabelsFixture extends TestFixture
{
    /**
     * fields property
     *
     * @var array<string, mixed>
     */
    public $fields = [
        'todo_item_id' => ['type' => 'integer', 'null' => false],
        'todo_label_id' => ['type' => 'integer', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['todo_item_id', 'todo_label_id']],
            'item_fk' => [
                'type' => 'foreign',
                'columns' => ['todo_item_id'],
                'references' => ['todo_items', 'id'],
                'update' => 'cascade',
                'delete' => 'cascade',
            ],
            'label_fk' => [
                'type' => 'foreign',
                'columns' => ['todo_label_id'],
                'references' => ['todo_labels', 'id'],
                'update' => 'cascade',
                'delete' => 'cascade',
            ],
        ],
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [];
}
