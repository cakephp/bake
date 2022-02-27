<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\Controller\Component;

use Bake\Test\App\Controller\Component\AppleComponent;
use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;

/**
 * Bake\Test\App\Controller\Component\AppleComponent Test Case
 */
class AppleComponentTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Bake\Test\App\Controller\Component\AppleComponent
     */
    protected $Apple;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->Apple = new AppleComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Apple);

        parent::tearDown();
    }

    /**
     * Test startup method
     *
     * @return void
     * @uses \Bake\Test\App\Controller\Component\AppleComponent::startup()
     */
    public function testStartup(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
