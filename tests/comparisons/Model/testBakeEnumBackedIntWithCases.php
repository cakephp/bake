<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;
use Cake\Database\Type\EnumLabelTrait;

/**
 * FooBar Enum
 */
enum FooBar: int implements EnumLabelInterface
{
    use EnumLabelTrait;

    case Foo = 0;
    case Bar = 1;
    case BarBaz = 9;
}
