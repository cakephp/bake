<?php
declare(strict_types=1);

namespace Bake\Test\App\Service;

/**
 * User service with required constructor args for testing
 */
class UserService
{
    /**
     * Constructor
     *
     * @param string $apiKey API key for the service
     * @param array $config Configuration options
     */
    public function __construct(
        protected string $apiKey,
        protected array $config = [],
    ) {
    }

    /**
     * Get user by ID
     *
     * @param int $id User ID
     * @return array User data
     */
    public function getUserById(int $id): array
    {
        return ['id' => $id];
    }

    /**
     * Create a new user
     *
     * @param array $data User data
     * @return bool Success status
     */
    public function createUser(array $data): bool
    {
        return true;
    }
}
