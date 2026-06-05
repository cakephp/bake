<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;
use Cake\Database\Type\EnumLabelTrait;

/**
 * BakeUserNullableGender Enum
 */
enum BakeUserNullableGender: string implements EnumLabelInterface
{
    use EnumLabelTrait;

    case Male = 'male';
    case Female = 'female';
    case Diverse = 'diverse';
}
