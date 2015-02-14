<?php
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
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Shell\Task;

use Bake\Test\TestCase\TestCase;
use Cake\Core\Plugin;

/**
 * BakeTemplateTaskTest class
 */
class BakeTemplateTaskTest extends TestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'BakeTemplate' . DS;
        $io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Task = $this->getMock(
            'Bake\Shell\Task\BakeTemplateTask',
            ['in', 'err', 'createFile', '_stop', 'clear'],
            [$io]
        );
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->Task);
        Plugin::unload('TestBakeTheme');
    }

    /**
     * test generate
     *
     * @return void
     */
    public function testGenerate()
    {
        $this->Task->expects($this->any())->method('in')->will($this->returnValue(1));

        $result = $this->Task->generate('example', ['test' => 'foo']);
        $this->assertSameAsFile(__FUNCTION__ . '.ctp', $result);
    }

    /**
     * test generate with an overriden template it gets used
     *
     * @return void
     */
    public function testGenerateWithTemplateOverride()
    {
        $this->_loadTestPlugin('TestBakeTheme');
        $this->Task->params['theme'] = 'TestBakeTheme';
        $this->Task->set([
            'plugin' => 'Special'
        ]);
        $result = $this->Task->generate('config/routes');
        $this->assertSameAsFile(__FUNCTION__ . '.ctp', $result);
    }
    /**
     * test generate with a missing template in the chosen template.
     * ensure fallback to default works.
     *
     * @return void
     */
    public function testGenerateWithTemplateFallbacks()
    {
        $this->_loadTestPlugin('TestBakeTheme');
        $this->Task->params['theme'] = 'TestBakeTheme';
        $this->Task->set([
            'name' => 'Articles',
            'table' => 'articles',
            'import' => false,
            'records' => false,
            'schema' => '',
            'namespace' => ''
        ]);
        $result = $this->Task->generate('tests/fixture');
        $this->assertSameAsFile(__FUNCTION__ . '.ctp', $result);
    }
}
