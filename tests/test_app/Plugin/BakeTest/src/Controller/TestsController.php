<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.1.0
 * @license   https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace BakeTest\Controller;

use Bake\Test\App\Controller\AppController;

/**
 * Class TestsController
 */
class TestsController extends AppController
{
    public function initialize(): void
    {
        $this->loadComponent('BakeTest.Plugins');
        $this->setHelpers(['BakeTest.OtherHelper', 'Html']);
    }

    public function index()
    {
        $this->set('test_value', 'It is a variable');
    }

    public function some_method()
    {
        $this->response->body(25);

        return $this->response;
    }
}
