<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
<<<<<<< HEAD:tests/test_app/App/Shell/Task/AppPolicyTask.php
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.1.0
 * @license   https://www.opensource.org/licenses/mit-license.php MIT License
=======
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     2.0.0
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
>>>>>>> cake5:tests/test_app/App/Command/AppPolicyCommand.php
 */
namespace Bake\Test\App\Command;

use Bake\Command\BakeCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

class AppPolicyCommand extends BakeCommand
{
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out('App Policy Generated');

        return static::CODE_SUCCESS;
    }
}
