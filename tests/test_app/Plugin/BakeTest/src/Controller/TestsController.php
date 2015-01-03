<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.1.0
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace BakeTest\Controller;

/**
 * Class TestsController
 */
class TestsController extends BakeTestAppController
{
    public $helpers = ['BakeTest.OtherHelper', 'Html'];

    public $components = ['BakeTest.Plugins'];

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
