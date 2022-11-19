<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\View;

use Bake\View\BakeView;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest as Request;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\TestCase;

/**
 * BakeViewTest class
 */
class BakeViewTest extends TestCase
{
    use StringCompareTrait;

    /**
     * @var BakeView
     */
    protected $View;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'BakeView' . DS;

        $request = new Request();
        $response = new Response();
        $this->View = new BakeView($request, $response);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->View);
    }

    /**
     * test rendering a template file
     *
     * @return void
     */
    public function testRenderTemplate()
    {
        $this->View->set(['aVariable' => 123]);
        $result = $this->View->render('simple');
        $expected = "The value of aVariable is: 123.\n";

        $this->assertSame($expected, $result, 'variables in erb-style tags should be evaluated');
    }

    /**
     * verify that php tags are ignored
     *
     * @return void
     */
    public function testRenderIgnorePhpTags()
    {
        $this->View->set(['aVariable' => 123]);
        $result = $this->View->render('simple_php');
        $expected = "The value of aVariable is: 123. Not <?php echo \$aVariable ?>.\n";

        $this->assertSame($expected, $result, 'variables in php tags should be treated as strings');
    }

    /**
     * verify that short php tags are ignored
     *
     * @return void
     */
    public function testRenderIgnorePhpShortTags()
    {
        $this->View->set(['aVariable' => 123]);
        $result = $this->View->render('simple_php_short_tags');
        $expected = "The value of aVariable is: 123. Not <?= \$aVariable ?>.\n";

        $this->assertSame($expected, $result, 'variables in php tags should be treated as strings');
    }

    /**
     * Newlines after template tags should act predictably
     *
     * @return void
     */
    public function testRenderNewlines()
    {
        $result = $this->View->render('newlines');
        $expected = "There should be a newline about here: \n";
        $expected .= "And this should be on the next line.\n";
        $expected .= "\n";
        $expected .= 'There should be no new line after this';

        $this->assertSame(
            $expected,
            $result,
            'Tags at the end of a line should not swallow new lines when rendered'
        );
    }

    /**
     * Verify that template tags with leading whitespace don't leave a mess
     *
     * @return void
     */
    public function testSwallowLeadingWhitespace()
    {
        $result = $this->View->render('leading_whitespace');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Verify that template tags don't inject newlines
     *
     * @return void
     */
    public function testNoLineBreaks()
    {
        $result = $this->View->render('no_line_breaks');
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * Verify that the proper events are dispatched when rendering a template,
     * irrespective of the used directory separator.
     *
     * @return void
     */
    public function testSeparatorRenderEvents()
    {
        $this->View->set('test', 'success');
        $result = $this->View->render('Custom' . DS . 'file');
        $this->assertSame(
            'success' . "\n",
            $result
        );

        $this->View->set('test', 'success');
        $this->View->getEventManager()->on('Bake.beforeRender.Custom.file', function (Event $event) {
            $event->getSubject()->set('test', 'separator constant beforeRender');
        });
        $this->View->getEventManager()->on('Bake.afterRender.Custom.file', function (Event $event) {
            $event->getSubject()->set('test', 'separator constant afterRender');
        });
        $result = $this->View->render('Custom' . DS . 'file');
        $this->assertSame(
            'separator constant beforeRender' . "\n",
            $result
        );
        $this->assertSame($this->View->get('test'), 'separator constant afterRender');

        $this->View->set('test', 'success');
        $this->View->getEventManager()->on('Bake.beforeRender.Custom.file', function (Event $event) {
            $event->getSubject()->set('test', 'fixed separator beforeRender');
        });
        $this->View->getEventManager()->on('Bake.afterRender.Custom.file', function (Event $event) {
            $event->getSubject()->set('test', 'fixed separator afterRender');
        });
        $result = $this->View->render('Custom/file');
        $this->assertSame(
            'fixed separator beforeRender' . "\n",
            $result
        );
        $this->assertSame($this->View->get('test'), 'fixed separator afterRender');
    }

    /**
     * Verify that the proper events are dispatched when rendering a plugin template.
     *
     * @return void
     */
    public function testPluginRenderEvents()
    {
        $this->View->set('test', 'success');
        $result = $this->View->render('Bake.Custom' . DS . 'file');
        $this->assertSame(
            'success' . "\n",
            $result
        );

        $this->View->set('test', 'success');
        $this->View->getEventManager()->on('Bake.beforeRender.Custom.file', function (Event $event) {
            $event->getSubject()->set('test', 'plugin template beforeRender');
        });
        $this->View->getEventManager()->on('Bake.afterRender.Custom.file', function (Event $event) {
            $event->getSubject()->set('test', 'plugin template afterRender');
        });
        $result = $this->View->render('Bake.Custom' . DS . 'file');
        $this->assertSame(
            'plugin template beforeRender' . "\n",
            $result
        );
        $this->assertSame($this->View->get('test'), 'plugin template afterRender');
    }

    /**
     * Verify that the proper events are dispatched when rendering a template.
     *
     * @return void
     */
    public function testCustomRenderEvents()
    {
        $this->View->set('test', 'success');
        $result = $this->View->render('Custom' . DS . 'file');
        $this->assertSame(
            'success' . "\n",
            $result
        );

        $this->View->set('test', 'success');
        $this->View->getEventManager()->on('Bake.beforeRender.Custom.file', function (Event $event) {
            $event->getSubject()->set('test', 'custom template beforeRender');
        });
        $this->View->getEventManager()->on('Bake.afterRender.Custom.file', function (Event $event) {
            $event->getSubject()->set('test', 'custom template afterRender');
        });
        $result = $this->View->render('Custom' . DS . 'file');
        $this->assertSame(
            'custom template beforeRender' . "\n",
            $result
        );
        $this->assertSame($this->View->get('test'), 'custom template afterRender');
    }

    /**
     * Ensure that application override templates don't have a double path in them.
     *
     * @return void
     */
    public function testApplicationOverride()
    {
        $result = $this->View->render('Bake.override');
        $this->assertSame("Application override.\n", $result);
    }
}
