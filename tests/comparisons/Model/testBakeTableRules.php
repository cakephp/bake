<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UniqueFields Model
 *
 * @method \Bake\Test\App\Model\Entity\UniqueField newEmptyEntity()
 * @method \Bake\Test\App\Model\Entity\UniqueField newEntity(array $data, array $options = [])
 * @method array<\Bake\Test\App\Model\Entity\UniqueField> newEntities(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Bake\Test\App\Model\Entity\UniqueField findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Bake\Test\App\Model\Entity\UniqueField> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\UniqueField>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\UniqueField>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\UniqueField>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\UniqueField> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\UniqueField>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\UniqueField>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\UniqueField>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\UniqueField> deleteManyOrFail(iterable $entities, array $options = [])
 */
class UniqueFieldsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('unique_fields');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);
        $rules->add($rules->isUnique(['field_1', 'field_2'], ['allowMultipleNulls' => true]), ['errorField' => 'field_1']);

        return $rules;
    }

    /**
     * Returns the database connection name to use by default.
     *
     * @return string
     */
    public static function defaultConnectionName(): string
    {
        return 'test';
    }
}
