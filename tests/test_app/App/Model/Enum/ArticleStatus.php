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
 * @since         3.0.4
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\App\Model\Enum;

enum ArticleStatus: string
{
    case PUBLISHED = 'Y';
    case UNPUBLISHED = 'N';
}
