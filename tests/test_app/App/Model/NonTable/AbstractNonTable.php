<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\NonTable;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;

/**
 * An abstract non-table model with stubbed methods demanded by RepositoryInterface
 */
abstract class AbstractNonTable implements RepositoryInterface
{
    public function setAlias(string $alias)
    {
    }

    public function getAlias(): string
    {
        return substr(static::class, strrpos(static::class, '\\') + 1);
    }

    public function setRegistryAlias(string $registryAlias)
    {
    }

    public function getRegistryAlias(): string
    {
    }

    public function hasField(string $field): bool
    {
    }

    public function find(string $type = 'all', array $options = [])
    {
    }

    public function get($primaryKey, array $options = []): EntityInterface
    {
    }

    public function query()
    {
    }

    public function updateAll($fields, $conditions): int
    {
    }

    public function deleteAll($conditions): int
    {
    }

    public function exists($conditions): bool
    {
    }

    public function save(EntityInterface $entity, $options = [])
    {
    }

    public function delete(EntityInterface $entity, $options = []): bool
    {
    }

    public function newEmptyEntity(): EntityInterface
    {
    }

    public function newEntity(array $data, array $options = []): EntityInterface
    {
    }

    public function newEntities(array $data, array $options = []): array
    {
    }

    public function patchEntity(EntityInterface $entity, array $data, array $options = []): EntityInterface
    {
    }

    public function patchEntities(iterable $entities, array $data, array $options = []): array
    {
    }
}
