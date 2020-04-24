<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         1.3.6
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Command;

/**
 * Middleware code generator.
 */
class MiddlewareCommand extends SimpleBakeCommand
{
    /**
     * Task name used in path generation.
     *
     * @var string
     */
    public $pathFragment = 'Middleware/';

    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'middleware';
    }

    /**
     * @inheritDoc
     */
    public function fileName(string $name): string
    {
        return $name . 'Middleware.php';
    }

    /**
     * @inheritDoc
     */
    public function template(): string
    {
        return 'Bake.Middleware/middleware';
    }
}
