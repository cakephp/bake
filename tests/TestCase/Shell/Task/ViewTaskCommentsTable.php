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
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Shell\Task;

use Cake\ORM\Table;

/**
 * Test View Task Comment Model
 */
class ViewTaskCommentsTable extends Table
{
    public function initialize(array $config)
    {
        $this->table('comments');
        $this->belongsTo('Articles', [
            'foreignKey' => 'article_id'
        ]);
    }
}
