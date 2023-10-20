<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \Bake\Test\App\Model\Table\CommentsTable&\Cake\ORM\Association\HasMany $Comments
 * @property \Bake\Test\App\Model\Table\TodoItemsTable&\Cake\ORM\Association\HasMany $TodoItems
 *
 * @method \Bake\Test\App\Model\Entity\User newEmptyEntity()
 * @method \Bake\Test\App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method array<\Bake\Test\App\Model\Entity\User> newEntities(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Bake\Test\App\Model\Entity\User findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \Bake\Test\App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Bake\Test\App\Model\Entity\User> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Bake\Test\App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\User>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\User> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\User>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\User> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
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

        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Comments', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('TodoItems', [
            'foreignKey' => 'user_id',
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
            ->scalar('username')
            ->maxLength('username', 255)
            ->allowEmptyString('username');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->allowEmptyString('password');

        return $validator;
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

        return $rules;
    }
}
