<?php
namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TodoTasks Model
 *
 * @property \Bake\Test\App\Model\Table\TodoItemsTable|\Cake\ORM\Association\BelongsTo $TodoItems
 *
 * @method \Bake\Test\App\Model\Entity\TodoTask get($primaryKey, $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoTask newEntity($data = null, array $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoTask[] newEntities(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoTask|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoTask saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoTask patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoTask[] patchEntities($entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoTask findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\CounterCacheBehavior
 */
class TodoTasksTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('todo_tasks');
        $this->setDisplayField('title');
        $this->setPrimaryKey('uid');

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', [
            'TodoItems' => ['todo_task_count'],
        ]);

        $this->belongsTo('TodoItems', [
            'foreignKey' => 'todo_item_id',
            'joinType' => 'INNER',
        ]);
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
        $rules->add($rules->existsIn(['todo_item_id'], 'TodoItems'));

        return $rules;
    }
}
