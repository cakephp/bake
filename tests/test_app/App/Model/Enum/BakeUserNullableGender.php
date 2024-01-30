<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;
use Cake\Utility\Inflector;

/**
 * BakeUserNullableGender Enum
 */
enum BakeUserNullableGender: string implements EnumLabelInterface
{
    case Male = 'male';
    case Female = 'female';
    case Diverse = 'diverse';

    /**
     * @return string
     */
    public function label(): string
    {
        return Inflector::humanize(Inflector::underscore($this->name));
    }
}
