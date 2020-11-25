<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     2.3.0
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace WithBakeSubFolder\Command;

use Bake\Command\BakeCommand;
use Exception;

/**
 * Test stub for command discovery
 */
class TheWhoCommand extends BakeCommand
{
    public static function defaultName(): string
    {
        throw new Exception('This command should not be loaded as there\'s a "Bake" subfolder');
    }
}
