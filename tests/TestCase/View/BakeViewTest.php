<?php
/**
 * CakePHP(tm) Tests <http://book.cakephp.org/2.0/en/development/testing.html>
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/2.0/en/development/testing.html CakePHP(tm) Tests
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\View;

use Bake\View\BakeView;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\TestCase;

/**
 * BakeViewTest class
 *
 */
class BakeViewTest extends TestCase
{
    use StringCompareTrait;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Bake') . 'tests' . DS . 'comparisons' . DS . 'BakeView' . DS;

        $request = new Request();
        $response = new Response();
        $this->View = new BakeView($request, $response);

        Configure::write(
            'App.paths.templates.x',
            Plugin::path('Bake') . 'tests' . DS . 'test_app' . DS . 'App' . DS . 'Template' . DS
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
        $expected .= "There should be no new line after this";

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
}
