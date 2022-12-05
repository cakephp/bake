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
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Utility;

use Bake\Test\TestCase\TestCase;
use Bake\Utility\TemplateRenderer;
use Cake\Core\Plugin;

/**
 * TemplateRendererTest class
 */
class TemplateRendererTest extends TestCase
{
    /**
     * @var \Bake\Utility\TemplateRenderer
     */
    protected $renderer;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'TemplateRenderer' . DS;
        $this->renderer = new TemplateRenderer();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->renderer);
        $this->removePlugins(['TestBakeTheme']);
    }

    /**
     * test generate
     *
     * @return void
     */
    public function testGenerate()
    {
        $result = $this->renderer->generate('example', ['test' => 'foo']);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * test generate with an overriden template it gets used
     *
     * @return void
     */
    public function testGenerateWithTemplateOverride()
    {
        $this->_loadTestPlugin('TestBakeTheme');
        $renderer = new TemplateRenderer('TestBakeTheme');
        $renderer->set([
            'plugin' => 'Special',
        ]);
        $result = $renderer->generate('config/routes');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
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
        $renderer = new TemplateRenderer('TestBakeTheme');
        $renderer->set([
            'name' => 'Articles',
            'table' => 'articles',
            'import' => false,
            'records' => false,
            'schema' => '',
            'namespace' => '',
        ]);
        $result = $renderer->generate('Bake.tests/fixture');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }
}
