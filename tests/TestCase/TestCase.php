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
namespace Bake\Test\TestCase;

use Cake\Core\Plugin;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\TestCase as ParentTestCase;

abstract class TestCase extends ParentTestCase
{
    use StringCompareTrait;

    /**
     * Load a plugin from the tests folder, and add to the autoloader
     *
     * @param string $name plugin name to load
     * @return void
     */
    protected function _loadTestPlugin($name)
    {
        $root = dirname(dirname(__FILE__)) . DS;
        $path = $root . 'test_app' . DS . 'Plugin' . DS . $name . DS;

        Plugin::load($name, [
            'path' => $path,
            'autoload' => true
        ]);
    }
}
