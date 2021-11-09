<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * ItemsPersistent Model
 *
 * @property \Bake\Test\App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \Bake\Test\App\Model\Table\TodoTasksTable&\Cake\ORM\Association\HasMany $TodoTasks
 * @property \Bake\Test\App\Model\Table\TodoLabelsTable&\Cake\ORM\Association\BelongsToMany $TodoLabels
 *
 * @method \Bake\Test\App\Model\Entity\Item newEmptyEntity()
 * @method \Bake\Test\App\Model\Entity\Item newEntity(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\Item[] newEntities(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\Item get($primaryKey, $options = [])
 * @method \Bake\Test\App\Model\Entity\Item findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \Bake\Test\App\Model\Entity\Item patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\Item[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\Item|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\Item saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\Item[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \Bake\Test\App\Model\Entity\Item[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \Bake\Test\App\Model\Entity\Item[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \Bake\Test\App\Model\Entity\Item[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
abstract class ItemsPersistentTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void {}

    /**
     * Persistent validation rules. Rules from ItemsTable::validationDefault()
     * are applied before this.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity. Rules from ItemsTable::buildRules()
     * are applied before this.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules;
    }
}
