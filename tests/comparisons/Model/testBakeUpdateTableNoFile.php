<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TodoItems Model
 *
 * @property \Bake\Test\App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \Bake\Test\App\Model\Table\TodoRemindersTable&\Cake\ORM\Association\HasOne $TodoReminders
 * @property \Bake\Test\App\Model\Table\TodoTasksTable&\Cake\ORM\Association\HasMany $TodoTasks
 * @property \Bake\Test\App\Model\Table\TodoLabelsTable&\Cake\ORM\Association\BelongsToMany $TodoLabels
 *
 * @method \Bake\Test\App\Model\Entity\TodoItem newEmptyEntity()
 * @method \Bake\Test\App\Model\Entity\TodoItem newEntity(array $data, array $options = [])
 * @method array<\Bake\Test\App\Model\Entity\TodoItem> newEntities(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoItem get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Bake\Test\App\Model\Entity\TodoItem findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoItem patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Bake\Test\App\Model\Entity\TodoItem> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoItem|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoItem saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\TodoItem>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\TodoItem>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\TodoItem>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\TodoItem> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\TodoItem>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\TodoItem>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\TodoItem>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\TodoItem> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TodoItemsTable extends Table
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

        $this->setTable('todo_items');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->hasOne('TodoReminders', [
            'foreignKey' => 'todo_item_id',
        ]);
        $this->hasMany('TodoTasks', [
            'foreignKey' => 'todo_item_id',
        ]);
        $this->belongsToMany('TodoLabels', [
            'foreignKey' => 'todo_item_id',
            'targetForeignKey' => 'todo_label_id',
            'joinTable' => 'todo_items_todo_labels',
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
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('title')
            ->maxLength('title', 50)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('body')
            ->allowEmptyString('body');

        $validator
            ->decimal('effort')
            ->notEmptyString('effort');

        $validator
            ->boolean('completed')
            ->notEmptyString('completed');

        $validator
            ->integer('todo_task_count')
            ->notEmptyString('todo_task_count');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

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
