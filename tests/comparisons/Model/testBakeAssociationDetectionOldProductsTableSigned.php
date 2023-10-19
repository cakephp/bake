<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OldProducts Model
 *
 * @method \Bake\Test\App\Model\Entity\OldProduct newEmptyEntity()
 * @method \Bake\Test\App\Model\Entity\OldProduct newEntity(array $data, array $options = [])
 * @method array<\Bake\Test\App\Model\Entity\OldProduct> newEntities(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\OldProduct get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Bake\Test\App\Model\Entity\OldProduct findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \Bake\Test\App\Model\Entity\OldProduct patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Bake\Test\App\Model\Entity\OldProduct> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\OldProduct|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Bake\Test\App\Model\Entity\OldProduct saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\OldProduct>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\OldProduct>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\OldProduct>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\OldProduct> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\OldProduct>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\OldProduct>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\OldProduct>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\OldProduct> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OldProductsTable extends Table
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

        $this->setTable('old_products');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 100)
            ->notEmptyString('name');

        return $validator;
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
