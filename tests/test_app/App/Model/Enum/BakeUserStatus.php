<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @since         3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;

enum BakeUserStatus: int implements EnumLabelInterface
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    /**
     * @return string
     */
    public function label(): string
    {
        return mb_strtolower($this->name);
    }
}
