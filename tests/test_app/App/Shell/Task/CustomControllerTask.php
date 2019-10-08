<?php
namespace Bake\Test\App\Shell\Task;

use Bake\Shell\Task\SimpleBakeTask;

/**
 * Test for task core
 */
class CustomControllerTask extends SimpleBakeTask
{
    public function name()
    {
        return 'CustomController';
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
