<%
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
 * @since         1.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
%>
<?php
namespace <%= $namespace %>\Shell\Helper;

use Cake\Console\Helper;

/**
 * <%= $name %> shell helper.
 */
class <%= $name %>Helper extends Helper
{

    /**
     * Output method.
     *
     * Generate the output for this shell helper.
     *
     * @return void
     */
    public function output($args)
    {
    }
}
