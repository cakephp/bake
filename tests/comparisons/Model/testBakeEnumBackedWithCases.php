<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;
use Cake\Utility\Inflector;

/**
 * FooBar Enum
 */
enum FooBar: string implements EnumLabelInterface
{
    case Foo = 'foo';
    case Bar = 'b';
    case BarBaz = 'bar_baz';

    /**
     * @return string
     */
    public function label(): string
    {
        return Inflector::humanize(Inflector::underscore($this->name));
    }
}
