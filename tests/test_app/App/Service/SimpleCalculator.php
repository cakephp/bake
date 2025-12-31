<?php
declare(strict_types=1);

namespace Bake\Test\App\Service;

/**
 * Simple calculator service for testing
 */
class SimpleCalculator
{
    /**
     * Add two numbers
     *
     * @param int $a First number
     * @param int $b Second number
     * @return int Sum of the two numbers
     */
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

    /**
     * Subtract two numbers
     *
     * @param int $a First number
     * @param int $b Second number
     * @return int Difference of the two numbers
     */
    public function subtract(int $a, int $b): int
    {
        return $a - $b;
    }
}
