<?php
/**
 * Created by javier
 * Date: 31/01/16
 * Time: 10:59
 */

namespace Bake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Class BakeTemplateRolesFixture
 * @package Bake\Test\Fixture
 */
class BakeTemplateRolesFixture extends TestFixture
{
    /**
     * @var string
     */
    public $table = 'roles';
    
    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string', 'null' => false],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['name' => 'admin'],
        ['name' => 'user'],
    ];
}
