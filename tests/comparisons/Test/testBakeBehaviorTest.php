<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\TestCase\Model\Behavior;

use Bake\Test\App\Model\Behavior\ExampleBehavior;
use Cake\ORM\Table;
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
    protected function setUp(): void
    {
        parent::setUp();
        $table = new Table();
        $this->Example = new ExampleBehavior($table);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Example);

        parent::tearDown();
    }
}
