<?php
declare(strict_types=1);

namespace Bake\Test\App\Command;

use Bake\Command\SimpleBakeCommand;

/**
 * Test for task core
 */
class CustomControllerCommand extends SimpleBakeCommand
{
    protected string $pathFragment = 'Controller/';

    public function name(): string
    {
        return 'Controller';
    }

    public function fileName(string $name): string
    {
        return $name . 'CustomController.php';
    }

    public function template(): string
    {
        return 'CustomController';
    }
}
