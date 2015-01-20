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
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
%>
{
    "name": "your-name-here/<%= $plugin %>",
    "description": "<%= $plugin %> plugin for CakePHP",
    "type": "cakephp-plugin",
    "require": {
        "php": ">=5.4",
        "cakephp/cakephp": "3.0.x-dev"
    },
    "require-dev": {
        "phpunit/phpunit": "*"
    },
    "autoload": {
        "psr-4": {
            "<%= $plugin %>\\": "src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "<%= $plugin %>\\Test\\": "tests",
            "Cake\\Test\\": "./vendor/cakephp/cakephp/tests"
        }
    }
}
