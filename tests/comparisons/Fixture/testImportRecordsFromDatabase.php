<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 *
 */
class UsersFixture extends TestFixture
{

    /**
     * Import
     *
     * @var array
     */
    public $import = ['model' => 'Users', 'connection' => 'test'];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 1,
            'username' => 'mariano',
            'password' => '$2a$10$u05j8FjsvLBNdfhBhc21LOuVMpzpabVXQ9OpC2wO3pSO0q6t7HHMO',
            'created' => '2007-03-17 01:16:23',
            'updated' => '2007-03-17 01:18:31'
        ],
        [
            'id' => 2,
            'username' => 'nate',
            'password' => '$2a$10$u05j8FjsvLBNdfhBhc21LOuVMpzpabVXQ9OpC2wO3pSO0q6t7HHMO',
            'created' => '2008-03-17 01:18:23',
            'updated' => '2008-03-17 01:20:31'
        ],
        [
            'id' => 3,
            'username' => 'larry',
            'password' => '$2a$10$u05j8FjsvLBNdfhBhc21LOuVMpzpabVXQ9OpC2wO3pSO0q6t7HHMO',
            'created' => '2010-05-10 01:20:23',
            'updated' => '2010-05-10 01:22:31'
        ],
        [
            'id' => 4,
            'username' => 'garrett',
            'password' => '$2a$10$u05j8FjsvLBNdfhBhc21LOuVMpzpabVXQ9OpC2wO3pSO0q6t7HHMO',
            'created' => '2012-06-10 01:22:23',
            'updated' => '2012-06-12 01:24:31'
        ],
    ];
}
