<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.3.6
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Shell\Task;

/**
 * Middleware code generator.
 *
 * @property \Bake\Shell\Task\BakeTemplateTask $BakeTemplate
 * @property \Bake\Shell\Task\TestTask $Test
 */
class MiddlewareTask extends SimpleBakeTask
{
    /**
     * Task name used in path generation.
     *
     * @var string
     */
    public $pathFragment = 'Middleware/';

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return 'middleware';
    }

    /**
     * {@inheritDoc}
     */
    public function fileName($name)
    {
        return $name . 'Middleware.php';
    }

    /**
     * {@inheritDoc}
     */
    public function template()
    {
        return 'Middleware/middleware';
    }
}
