<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TestBakeArticles Model
 *
 * @method \Bake\Test\App\Model\Entity\TestBakeArticle newEmptyEntity()
 * @method \Bake\Test\App\Model\Entity\TestBakeArticle newEntity(array $data, array $options = [])
 * @method array<\Bake\Test\App\Model\Entity\TestBakeArticle> newEntities(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\TestBakeArticle get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Bake\Test\App\Model\Entity\TestBakeArticle findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \Bake\Test\App\Model\Entity\TestBakeArticle patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Bake\Test\App\Model\Entity\TestBakeArticle> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\TestBakeArticle|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Bake\Test\App\Model\Entity\TestBakeArticle saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\TestBakeArticle>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\TestBakeArticle>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\TestBakeArticle>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\TestBakeArticle> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\TestBakeArticle>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\TestBakeArticle>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\TestBakeArticle>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\TestBakeArticle> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TestBakeArticlesTable extends Table
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

        $this->setTable('bake_articles');
        $this->setDisplayField('title');
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
            ->numeric('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 100, 'Name must be shorter than 100 characters.')
            ->requirePresence('name', 'create')
            ->allowEmptyString('name', null, false);

        $validator
            ->nonNegativeInteger('count')
            ->requirePresence('count', 'create')
            ->allowEmptyString('count', null, false);

        $validator
            ->greaterThanOrEqual('price', 0)
            ->requirePresence('price', 'create')
            ->allowEmptyString('price', null, false);

        $validator
            ->email('email')
            ->add('email', 'unique', ['rule' => 'validateUnique', 'provider' => 'table'])
            ->allowEmptyString('email');

        $validator
            ->uploadedFile('image', [
                'optional' => true,
                'types' => ['image/jpeg'],
            ])
            ->allowEmptyFile('image');

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
