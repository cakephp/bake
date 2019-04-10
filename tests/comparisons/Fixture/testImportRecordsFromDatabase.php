<?php
namespace Bake\Test\App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Import
     *
     * @var array
     */
    public $import = ['table' => 'users', 'connection' => 'test'];

    /**
     * Init method
     *
     * @return void
     */
    public function init()
    {
        $this->records = [
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
        ];
        parent::init();
    }
}
