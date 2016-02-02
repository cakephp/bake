<?php
/**
 * CakePHP : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Shell\Task\TemplateTask;

use Cake\ORM\Table;

/**
 * Class AuthorsTable
 */
class AuthorsTable extends Table
{

    /**
     * @param array $config
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('bake_authors');
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id'
        ]);
        $this->hasMany('Articles', [
            'foreignKey' => 'author_id'
        ]);
        $this->hasOne('Profiles', [
            'foreignKey' => 'author_id'
        ]);
    }
}
