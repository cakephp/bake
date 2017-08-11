<?php
namespace Bake\Test\App\Shell\Task;

/**
 * Test for a task core overloaded
 */
class ControllerTask extends \Bake\Shell\Task\ControllerTask
{
    public function main($name = null)
    {
        // new code here

        parent::main($name);
    }
}
