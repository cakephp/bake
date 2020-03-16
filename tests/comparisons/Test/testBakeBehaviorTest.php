<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\Model\Behavior;

use Bake\Test\App\Model\Behavior\ExampleBehavior;
use Cake\TestSuite\TestCase;

/**
 * Bake\Test\App\Model\Behavior\ExampleBehavior Test Case
 */
class ExampleBehaviorTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Bake\Test\App\Model\Behavior\ExampleBehavior
     */
    protected $Example;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Example = new ExampleBehavior();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Example);

        parent::tearDown();
    }
}
