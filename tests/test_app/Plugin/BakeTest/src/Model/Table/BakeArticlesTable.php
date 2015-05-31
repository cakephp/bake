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
 * @since         1.0.6
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace BakeTest\Model\Table;

use Cake\ORM\Table;

/**
 * Class BakeArticle
 */
class BakeArticlesTable extends Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('BakeUsers');
        $this->hasMany('BakeComments');
        $this->belongsToMany('BakeTags');
    }
}
