<?php
declare(strict_types=1);

// Apply the project rules while checking only the files supplied by the generation tests.
return (require dirname(__DIR__) . '/rector.php')
    ->withPaths([])
    ->withCache(cacheDirectory: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bake-generated-rector');
