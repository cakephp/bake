<?php
declare(strict_types=1);
namespace Bake\Test\App\Command;

use Bake\Command\SimpleBakeCommand;

/**
 * Test for task core
 */
class CustomControllerCommand extends SimpleBakeCommand
{
    public $pathFragment = 'Controller/';

    public function name()
    {
        return 'Controller';
    }

    public function fileName($name)
    {
        return $name . 'CustomController.php';
    }

    public function template()
    {
        return 'CustomController';
    }
}
