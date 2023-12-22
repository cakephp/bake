<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Enum;

/**
 * FooBar Enum
 */
enum FooBar: int
{
    /**
     * @return string
     */
    public function label(): string
    {
        return mb_strtolower($this->name);
    }
}
