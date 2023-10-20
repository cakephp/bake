<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Categories Model
 *
 * @property \Bake\Test\App\Model\Table\ProductsTable&\Cake\ORM\Association\BelongsToMany $Products
 *
 * @method \Bake\Test\App\Model\Entity\Category newEmptyEntity()
 * @method \Bake\Test\App\Model\Entity\Category newEntity(array $data, array $options = [])
 * @method array<\Bake\Test\App\Model\Entity\Category> newEntities(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\Category get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Bake\Test\App\Model\Entity\Category findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \Bake\Test\App\Model\Entity\Category patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Bake\Test\App\Model\Entity\Category> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\Category|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Bake\Test\App\Model\Entity\Category saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\Category>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\Category>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\Category>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\Category> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\Category>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\Category>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\Category>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\Category> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CategoriesTable extends Table
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

        $this->setTable('categories');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsToMany('Products', [
            'foreignKey' => 'category_id',
            'targetForeignKey' => 'product_id',
            'joinTable' => 'categories_products',
        ]);
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
