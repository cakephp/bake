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

// @codingStandardsIgnoreFile

use Cake\Core\ClassLoader;
use Cake\Core\Configure;
use Cake\Core\Plugin;

$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);
    throw new Exception('Cannot find the root of the application, unable to run tests');
};
$root = $findRoot(__FILE__);
unset($findRoot);
chdir($root);

if (file_exists($root . '/config/bootstrap.php')) {
    require $root . '/config/bootstrap.php';
} else {
    require $root . '/vendor/cakephp/cakephp/tests/bootstrap.php';

    Plugin::load('Bake', [
        'path' => dirname(dirname(__FILE__)) . DS,
        'autoload' => true
    ]);

}

if (!defined('TESTS')) {
    define('TESTS', ROOT . DS . 'tests' . DS);
}

$testAppRoot = $root . DS . 'tests' . DS . 'test_app' . DS;

Configure::write('Bake', [
    'app' => $testAppRoot . 'App' . DS,
    'root' => rtrim($testAppRoot, DS)
]);

Configure::write('App.paths', [
    'plugins' => [$testAppRoot . 'Plugin' . DS],
    'templates' => [$testAppRoot . 'App' . DS . 'Template' . DS]
]);

$loader = new Cake\Core\ClassLoader;
$loader->register();

$loader->addNamespace('Bake\Test\App', $testAppRoot . 'App');
$loader->addNamespace('BakeTest', $testAppRoot . 'Plugin' . DS . 'BakeTest' . DS . 'src');
