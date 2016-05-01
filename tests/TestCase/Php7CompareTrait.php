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
 * @since         1.1.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase;

use Cake\TestSuite\StringCompareTrait;

trait Php7CompareTrait
{
    use StringCompareTrait {
        assertSameAsFile as assertSameAsFileBase;
    }

    /**
     * Compare the result to the contents of the file based upon which
     * version PHP is being used.
     *
     * The env variable UPDATE_TEST_COMPARISON_FILES will not generate "php7"
     * files. They must be created manually.
     *
     * @param string $path partial path to test comparison file
     * @param string $result test result as a string
     * @return void
     */
    public function assertSameAsFile($path, $result)
    {
        if (!file_exists($path)) {
            $path = $this->_compareBasePath . $path;
        }

        if (PHP_MAJOR_VERSION >= 7 && file_exists($path . '7')) {
            $path = $path . '7';
        }

        $this->assertSameAsFileBase($path, $result);
    }
}
