<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;
use Cake\Database\Type\EnumLabelTrait;

/**
 * FooBar Enum
 */
enum FooBar: string implements EnumLabelInterface
{
    use EnumLabelTrait;
}
