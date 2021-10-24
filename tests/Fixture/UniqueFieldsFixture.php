<?php
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
 * @since         2.6.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UniqueFiUniqueFieldsFixturexture
 */
class UniqueFieldsFixture extends TestFixture
{
    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'username' => ['type' => 'string', 'null' => true, 'length' => 255],
        'email' => ['type' => 'string', 'null' => true, 'length' => 255],
        'field_1' => ['type' => 'string', 'null' => true, 'length' => 255],
        'field_2' => ['type' => 'string', 'null' => true,'length' => 255],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
            'multiple_fields_unique' => [
                'type' => 'unique',
                'columns' => [
                    'field_1',
                    'field_2',
                ],
            ],
        ],
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['field_1' => 'unique_value_1', 'field_2' => 'unique_value_2'],
        ['field_1' => 'unique_value_2', 'field_2' => 'unique_value_3'],
    ];
}
