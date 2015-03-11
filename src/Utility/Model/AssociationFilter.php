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
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Utility\Model;

use \Cake\ORM\Table;

/**
 * Utility class to filter Model Table associations
 *
 */
class AssociationFilter {

/**
 * Detect existing belongsToMany associations and cleanup the hasMany aliases based on existing
 * belongsToMany associations provided
 *
 * @param \Cake\ORM\Table $table
 * @param array $aliases
 * @return array $aliases
 */
    public static function filterHasManyAssociationsAliases(Table $table, array $aliases) {
        $extractor = function ($val) {
            return $val->junction()->alias();
        };
        $belongsToManyJunctionsAliases = array_map($extractor, $table->associations()->type('BelongsToMany'));
        return array_values(array_diff($aliases, $belongsToManyJunctionsAliases));
    }


}
