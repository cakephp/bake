<?php
declare(strict_types=1);

namespace Bake\Test\App\Shell\Task;

use Bake\Shell\Task\BakeTask;

/**
 * Test for a task core overloaded
 */
class ControllerTask extends BakeTask
{
    public function main(): ?int
    {
        return static::CODE_SUCCESS;
    }
}
