<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;
use Cake\Utility\Inflector;

/**
 * FooBar Enum
 */
enum FooBar: int implements EnumLabelInterface
{
    case Foo = 0;
    case Bar = 1;
    case BarBaz = 9;

    /**
     * @return string
     */
    public function label(): string
    {
        return Inflector::humanize(Inflector::underscore($this->name));
    }
}
