<?php
namespace App\Test\TestCase\View\Helper;

use App\View\Helper\ExampleHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * App\View\Helper\ExampleHelper Test Case
 */
class ExampleHelperTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\View\Helper\ExampleHelper
     */
    public $Example;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $view = new View();
        $this->Example = new ExampleHelper($view);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Example);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
