<?php
namespace BakeTest\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \BakeTest\Model\Table\CommentsTable&\Cake\ORM\Association\HasMany $Comments
 * @property \BakeTest\Model\Table\CounterCachePostsTable&\Cake\ORM\Association\HasMany $CounterCachePosts
 *
 * @method \BakeTest\Model\Entity\User get($primaryKey, $options = [])
 * @method \BakeTest\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \BakeTest\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \BakeTest\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \BakeTest\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \BakeTest\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \BakeTest\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \BakeTest\Model\Entity\User findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Comments', [
            'foreignKey' => 'user_id',
            'className' => 'BakeTest.Comments',
        ]);
        $this->hasMany('CounterCachePosts', [
            'foreignKey' => 'user_id',
            'className' => 'BakeTest.CounterCachePosts',
        ]);
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['username']));

        return $rules;
    }
}
