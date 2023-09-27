<?php
declare(strict_types=1);

/**
 * CakePHP : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP Project
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\App\Model\Table;

use Cake\ORM\Table;

/**
 * Class AuthorsTable
 */
class AuthorsTable extends Table
{
    /**
     * @param array<string, mixed> $config
     * @return void
     */
    public function initialize(array $config): void
    {
        $this->setTable('bake_authors');
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('Articles', [
            'foreignKey' => 'author_id',
        ]);
        $this->hasOne('Profiles', [
            'foreignKey' => 'author_id',
        ]);
    }
}
