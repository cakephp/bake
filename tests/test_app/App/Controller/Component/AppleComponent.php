<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.1.0
 * @license   https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\App\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;

/**
 * AppleComponent class
 */
class AppleComponent extends Component
{
    /**
     * components property
     *
     * @var array
     */
    protected array $components = ['Orange'];

    /**
     * startup method
     *
     * @param  Event $event
     * @param  mixed $controller
     * @return void
     */
    public function startup(Event $event)
    {
    }
}
