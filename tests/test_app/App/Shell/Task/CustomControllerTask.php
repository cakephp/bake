<?php
declare(strict_types=1);
namespace Bake\Test\App\Shell\Task;

use Bake\Shell\Task\SimpleBakeTask;

/**
 * Test for task core
 */
class CustomControllerTask extends SimpleBakeTask
{
    public $pathFragment = 'Controller/';

    public function name(): string
    {
        return 'Controller';
    }

    public function fileName($name): string
    {
        return $name . 'CustomController.php';
    }

    public function template(): string
    {
        return 'CustomController';
    }
}
